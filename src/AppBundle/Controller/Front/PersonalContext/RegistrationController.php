<?php

namespace AppBundle\Controller\Front\PersonalContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\UserRegistrationPersonalVehicleDTO;
use AppBundle\Form\Type\UserRegistrationPersonalVehicleType;
use AppBundle\Services\Vehicle\PersonalVehicleEditionService;
use AppBundle\Utils\VehicleInfoAggregator;
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
    /** @var PersonalVehicleEditionService */
    protected $personalVehicleEditionService;
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
     * @param PersonalVehicleEditionService $personalVehicleEditionService
     * @param ApiConnector $autoDataConnector
     * @param ZipCode $zipCodeService
     * @param MessageBus $eventBus
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        PersonalVehicleRepository $vehicleRepository,
        VehicleInfoAggregator $vehicleInfoAggregator,
        PersonalVehicleEditionService $personalVehicleEditionService,
        ApiConnector $autoDataConnector,
        ZipCode $zipCodeService,
        MessageBus $eventBus
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleRepository = $vehicleRepository;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->personalVehicleEditionService = $personalVehicleEditionService;
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
                return $this->redirectToRoute('front_vehicle_pro_add', [
                    'plateNumber' => $plateNumber
                ]);
            }
        }

        $date1erCir = null;
        $vin = null;
        if ($plateNumber) {
            try {
                $information = $this->autoDataConnector->executeRequest(new GetInformationFromPlateNumber($plateNumber));
                $ktypNumber = $information['Vehicule']['LTYPVEH']['TYPVEH']['KTYPNR'] ?? null;
                $filters = $ktypNumber ? ['ktypNumber' => $ktypNumber] : $filters;
                $filters['make'] = $information['Vehicule']['MARQUE'];
                $filters['model'] = $information['Vehicule']['MODELE_ETUDE'];
                $filters['engine'] = $information['Vehicule']['VERSION'];
                $date1erCir = $information['Vehicule']['DATE_1ER_CIR'] ?? null;
                $vin = $information['Vehicule']['CODIF_VIN_PRF'] ?? null;
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
            ['available_values' => $availableValues]);

        $vehicleForm->handleRequest($request);

        if ($vehicleForm->isSubmitted() && $vehicleForm->isValid()) {
            try {
                $this->personalVehicleEditionService->createInformations($vehicleDTO, $this->getUser());
                return $this->redirectToRoute('register_confirm');
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
}
