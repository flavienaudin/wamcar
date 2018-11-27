<?php

namespace AppBundle\Controller\Front\PersonalContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Controller\Front\SecurityController;
use AppBundle\Form\DTO\UserRegistrationPersonalVehicleDTO;
use AppBundle\Form\Type\UserRegistrationPersonalVehicleType;
use AppBundle\Security\UserAuthenticator;
use AppBundle\Services\Vehicle\PersonalVehicleEditionService;
use AppBundle\Utils\VehicleInfoAggregator;
use AppBundle\Utils\VehicleInfoProvider;
use AutoData\ApiConnector;
use AutoData\Exception\AutodataException;
use AutoData\Exception\AutodataWithUserMessageException;
use AutoData\Request\GetInformationFromPlateNumber;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PragmaRX\ZipCode\ZipCode;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\PersonalVehicleRepository;

class RegistrationController extends BaseController
{
    const VEHICLE_REPLACE_PARAM = 'r';

    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var PersonalVehicleRepository */
    private $vehicleRepository;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;
    /** @var VehicleInfoProvider */
    private $vehicleInfoProvider;
    /** @var PersonalVehicleEditionService */
    protected $personalVehicleEditionService;
    /** @var UserAuthenticator */
    protected $userAuthenticator;
    /** @var ApiConnector */
    protected $autoDataConnector;
    /** @var ZipCode */
    protected $zipCodeService;
    /** @var MessageBus */
    protected $eventBus;

    /**
     * RegistrationController constructor.
     * @param FormFactoryInterface $formFactory
     * @param PersonalVehicleRepository $vehicleRepository
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     * @param VehicleInfoProvider $vehicleInfoProvider
     * @param PersonalVehicleEditionService $personalVehicleEditionService
     * @param UserAuthenticator $userAuthenticator
     * @param ApiConnector $autoDataConnector
     * @param ZipCode $zipCodeService
     * @param MessageBus $eventBus
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        PersonalVehicleRepository $vehicleRepository,
        VehicleInfoAggregator $vehicleInfoAggregator,
        VehicleInfoProvider $vehicleInfoProvider,
        PersonalVehicleEditionService $personalVehicleEditionService,
        UserAuthenticator $userAuthenticator,
        ApiConnector $autoDataConnector,
        ZipCode $zipCodeService,
        MessageBus $eventBus
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleRepository = $vehicleRepository;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->vehicleInfoProvider= $vehicleInfoProvider;
        $this->personalVehicleEditionService = $personalVehicleEditionService;
        $this->userAuthenticator = $userAuthenticator;
        $this->autoDataConnector = $autoDataConnector;
        $this->zipCodeService = $zipCodeService;
        $this->eventBus = $eventBus;
    }

    /**
     * @param Request $request
     * @param string $plateNumber
     * @return Response
     * @throws \Exception
     */
    public function vehicleRegistrationAction(Request $request, string $plateNumber = null): Response
    {
        $filters = $request->get('vehicle_information', []);
        unset($filters['_token']);

        $plateNumber = $plateNumber ?? $request->get('plate_number', null);

        if ($this->isUserAuthenticated()) {
            $user = $this->getUser();
            if($user instanceof PersonalUser) {
                return $this->redirectToRoute('front_vehicle_personal_add', [
                    'plateNumber' => $plateNumber
                ]);
            }elseif ($user instanceof ProUser){
                $userGarages = $user->getEnabledGarageMemberships();
                if($userGarages->count() > 1){
                    // Redirection vers profil pour choisir le garage auquel ajouter une nouveau véhicule
                    $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.select_garage_first');
                    return $this->redirectToRoute('front_view_current_user_info');
                }elseif($userGarages->count() == 1){
                    return $this->redirectToRoute('front_vehicle_pro_add', [
                        'garage_id' => $userGarages->first()->getId(),
                        'plateNumber' => $plateNumber
                    ]);
                }else{
                    $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.pro_user_need_garage');
                    return $this->redirectToRoute("front_garage_create");
                }
            }
        }

        $date1erCir = null;
        $vin = null;
        if ($plateNumber) {
            try {
                $information = $this->autoDataConnector->executeRequest(new GetInformationFromPlateNumber($plateNumber));
                $ktypNumber = null;
                if(isset($information['Vehicule']['ktypnr_aaa']) && !empty($information['Vehicule']['ktypnr_aaa'])){
                    $ktypNumber = $information['Vehicule']['ktypnr_aaa'];
                } elseif(isset($information['Vehicule']['LTYPVEH'])){
                    foreach ($information['Vehicule']['LTYPVEH'] as $key => $value){
                        if(isset($value['KTYPNR']) && !empty($value['KTYPNR'])){
                            if(!empty($ktypNumber)){
                                $this->session->getFlashBag()->add(
                                    self::FLASH_LEVEL_DANGER,
                                    'flash.warning.vehicle.multiple_vehicle_types'
                                );
                            }
                            $ktypNumber = $value['KTYPNR'];
                        }
                    }
                };

                $vehicleInfo = [];
                if(!empty($ktypNumber)) {
                    $vehicleInfo = $this->vehicleInfoProvider->getVehicleInfoByKtypNumber($ktypNumber);
                }
                if(count($vehicleInfo) == 1){
                    if(isset($vehicleInfo[0]['make']) && !empty($vehicleInfo[0]['make'])){
                        $filters['make'] = $vehicleInfo[0]['make'];
                    }else{
                        $filters['make'] = $information['Vehicule']['MARQUE'];
                    }
                    if(isset($vehicleInfo[0]['make']) && !empty($vehicleInfo[0]['model'])){
                        $filters['model'] = $vehicleInfo[0]['model'];
                    }else{
                        $filters['model'] = $information['Vehicule']['MODELE_ETUDE'];
                    }
                    if(isset($vehicleInfo[0]['make']) && !empty($vehicleInfo[0]['engine'])){
                        $filters['engine'] = $vehicleInfo[0]['engine'];
                    }else{
                        $filters['engine'] = $information['Vehicule']['VERSION'];
                    }

                }else {
                    $filters['make'] = $information['Vehicule']['MARQUE'];
                    $filters['model'] = $information['Vehicule']['MODELE_ETUDE'];
                    $filters['engine'] = $information['Vehicule']['VERSION'];
                }

                $date1erCir = $information['Vehicule']['DATE_1ER_CIR'] ?? null;
                $vin = $information['Vehicule']['CODIF_VIN_PRF'] ?? null;
                if($vin && strlen($vin) < 17){
                    $vin = str_pad($vin, 17, '_', STR_PAD_LEFT);
                }
            } catch (AutodataException $autodataException) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_DANGER,
                    $autodataException instanceof AutodataWithUserMessageException ?
                        $autodataException->getMessage() :
                        'flash.warning.registration_recognition_failed'
                );
            }
        }

        return $this->vehicleRegistrationFromInformation($request, $filters, $plateNumber, $date1erCir, $vin);
    }

    /**
     * @param Request $request
     * @param array $filters
     * @param string|null $plateNumber
     * @param string|null $date1erCir
     * @param string|null $vin
     * @return Response
     * @throws \Exception
     */
    private function vehicleRegistrationFromInformation(
        Request $request,
        array $filters = [],
        string $plateNumber = null,
        string $date1erCir = null,
        string $vin = null): Response
    {
        $vehicleDTO = new UserRegistrationPersonalVehicleDTO($plateNumber, $date1erCir, $vin);
        $vehicleDTO->vehicleReplace = (bool) $request->get('vehicle-replace', $vehicleDTO->vehicleReplace);
        $vehicleDTO->updateFromFilters($filters);

        $availableValues = $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel($filters);

        $vehicleForm = $this->formFactory->create(
            UserRegistrationPersonalVehicleType::class,
            $vehicleDTO,
            [
                'available_values' => $availableValues,
                'action' => $this->generateUrl('front_vehicle_registration', ['plateNumber' => $plateNumber])
            ]);

        $vehicleForm->handleRequest($request);

        if ($vehicleForm->isSubmitted() && $vehicleForm->isValid()) {
            try {
                $registeredVehicle = $this->personalVehicleEditionService->createInformations($vehicleDTO, $this->getUser());
                $this->userAuthenticator->authenticate($registeredVehicle->getOwner());
                return $this->redirectToRoute('register_confirm', [SecurityController::INSCRIPTION_QUERY_PARAM =>'personal-emaill']);
            } catch (UniqueConstraintViolationException $exception) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_DANGER,
                    'flash.danger.registration_duplicate'
                );
                // TODO aller directement à l'étape 4 d'inscription (garder le véhicule à créer)
            }
        }

        return $this->render(
            ':front/Security/Register:user_car.html.twig',
            [
                'vehicleForm' => $vehicleForm->createView(),
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateVehicleRegistrationFormAction(Request $request): JsonResponse
    {
        $filters = $request->get('filters', []);
        $fetch = $request->get('fetch', null);

        $aggregates = $this->vehicleInfoAggregator->getVehicleInfoAggregates($filters);

        return new JsonResponse($fetch ? $aggregates[$fetch] : $aggregates);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCityByZipcodeAction(Request $request): JsonResponse
    {
        $zipcode = $request->get('zipcode', null);
        $city = $this->zipCodeService->find($zipcode);

        return new JsonResponse($city->toArray());
    }



    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function registrationOrientationAction(Request $request): Response
    {
        // TODO

        return $this->render('front/Security/Register/orientation_personal_registration.html.twig');
    }
}
