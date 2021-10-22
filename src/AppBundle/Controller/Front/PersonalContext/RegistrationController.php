<?php

namespace AppBundle\Controller\Front\PersonalContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Controller\Front\SecurityController;
use AppBundle\Elasticsearch\Elastica\VehicleInfoEntityIndexer;
use AppBundle\Form\DTO\UserRegistrationPersonalVehicleDTO;
use AppBundle\Form\Type\PersonalRegistrationOrientationType;
use AppBundle\Form\Type\UserRegistrationPersonalVehicleType;
use AppBundle\Security\UserAuthenticator;
use AppBundle\Services\User\UserEditionService;
use AppBundle\Services\Vehicle\PersonalVehicleEditionService;
use AutoData\ApiConnector;
use AutoData\Exception\AutodataException;
use AutoData\Exception\AutodataWithUserMessageException;
use AutoData\Request\GetInformationFromPlateNumber;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\User\Enum\PersonalOrientationChoices;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\PersonalVehicleRepository;

class RegistrationController extends BaseController
{
    const PERSONAL_ORIENTATION_ACTION_SESSION_KEY = 'personal_orientation/action/choice';
    const VEHICLE_REPLACE_PARAM = 'r';

    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var PersonalVehicleRepository */
    private $vehicleRepository;
    /** @var VehicleInfoEntityIndexer */
    private $vehicleInfoEntityIndexer;
    /** @var PersonalVehicleEditionService */
    protected $personalVehicleEditionService;
    /** @var UserAuthenticator */
    protected $userAuthenticator;
    /** @var UserEditionService */
    protected $userEditionService;
    /** @var ApiConnector */
    protected $autoDataConnector;
    /** @var MessageBus */
    protected $eventBus;

    /**
     * RegistrationController constructor.
     * @param FormFactoryInterface $formFactory
     * @param PersonalVehicleRepository $vehicleRepository
     * @param VehicleInfoEntityIndexer $vehicleInfoIndexer
     * @param PersonalVehicleEditionService $personalVehicleEditionService
     * @param UserAuthenticator $userAuthenticator
     * @param UserEditionService $userEditionService
     * @param ApiConnector $autoDataConnector
     * @param MessageBus $eventBus
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        PersonalVehicleRepository $vehicleRepository,
        VehicleInfoEntityIndexer $vehicleInfoIndexer,
        PersonalVehicleEditionService $personalVehicleEditionService,
        UserAuthenticator $userAuthenticator,
        UserEditionService $userEditionService,
        ApiConnector $autoDataConnector,
        MessageBus $eventBus
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleRepository = $vehicleRepository;
        $this->vehicleInfoEntityIndexer = $vehicleInfoIndexer;
        $this->personalVehicleEditionService = $personalVehicleEditionService;
        $this->userAuthenticator = $userAuthenticator;
        $this->userEditionService = $userEditionService;
        $this->autoDataConnector = $autoDataConnector;
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
            if ($user instanceof PersonalUser) {
                return $this->redirectToRoute('front_vehicle_personal_add', [
                    'plateNumber' => $plateNumber
                ]);
            } elseif ($user instanceof ProUser) {
                $userGarages = $user->getEnabledGarageMemberships();
                if ($userGarages->count() > 1) {
                    // Redirection vers profil pour choisir le garage auquel ajouter une nouveau véhicule
                    $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.select_garage_first');
                    return $this->redirectToRoute('front_view_current_user_info');
                } elseif ($userGarages->count() == 1) {
                    return $this->redirectToRoute('front_vehicle_pro_add', [
                        'garage_id' => $userGarages->first()->getGarage()->getId(),
                        'plateNumber' => $plateNumber
                    ]);
                } else {
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
                if (isset($information['Vehicule']['ktypnr_aaa']) && !empty($information['Vehicule']['ktypnr_aaa'])) {
                    $ktypNumber = $information['Vehicule']['ktypnr_aaa'];
                } elseif (isset($information['Vehicule']['LTYPVEH'])) {
                    foreach ($information['Vehicule']['LTYPVEH'] as $key => $value) {
                        if (isset($value['KTYPNR']) && !empty($value['KTYPNR'])) {
                            if (!empty($ktypNumber)) {
                                $this->session->getFlashBag()->add(
                                    self::FLASH_LEVEL_DANGER,
                                    'flash.warning.vehicle.multiple_vehicle_types'
                                );
                            }
                            $ktypNumber = $value['KTYPNR'];
                        }
                    }
                };

                if (!empty($ktypNumber)) {
                    $vehicleInfoResultSet = $this->vehicleInfoEntityIndexer->getVehicleInfoByKtypNumber($ktypNumber);
                }
                if (isset($vehicleInfoResultSet) && $vehicleInfoResultSet->getTotalHits() == 1) {
                    $vehicleInfoResult = $vehicleInfoResultSet->getResults()[0];
                    $vehicleInfo = $vehicleInfoResult->getData();

                    if (isset($vehicleInfo['make']) && !empty($vehicleInfo['make'])) {
                        $filters['make'] = $vehicleInfo['make'];
                    } else {
                        $filters['make'] = $information['Vehicule']['MARQUE'];
                    }
                    if (isset($vehicleInfo['model']) && !empty($vehicleInfo['model'])) {
                        $filters['model'] = $vehicleInfo['model'];
                    } else {
                        $filters['model'] = $information['Vehicule']['MODELE_ETUDE'];
                    }
                    if (isset($vehicleInfo['engine']) && !empty($vehicleInfo['engine'])) {
                        $filters['engine'] = $vehicleInfo['engine'];
                    } else {
                        $filters['engine'] = $information['Vehicule']['VERSION'];
                    }
                } else {
                    $filters['make'] = $information['Vehicule']['MARQUE'];
                    $filters['model'] = $information['Vehicule']['MODELE_ETUDE'];
                    $filters['engine'] = $information['Vehicule']['VERSION'];
                }

                $date1erCir = $information['Vehicule']['DATE_1ER_CIR'] ?? null;
                $vin = $information['Vehicule']['CODIF_VIN_PRF'] ?? null;
                if ($vin && strlen($vin) < 17) {
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
        $vehicleDTO->vehicleReplace = (bool)$request->get('vehicle-replace', $vehicleDTO->vehicleReplace);
        $vehicleDTO->updateFromFilters($filters);

        $availableValues = $this->vehicleInfoEntityIndexer->getVehicleInfoAggregatesFromMakeAndModel($filters);

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
                return $this->redirectToRoute('register_orientation', [
                    SecurityController::INSCRIPTION_QUERY_PARAM => 'personal-emaill',
                    'vehicleReplace' => $vehicleDTO->vehicleReplace
                ]);
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

        $aggregates = $this->vehicleInfoEntityIndexer->getVehicleInfoAggregates($filters);

        return new JsonResponse($fetch ? $aggregates[$fetch] : $aggregates);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function registrationOrientationAction(Request $request): Response
    {
        if (!$this->getUser() instanceof PersonalUser) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_DANGER,
                'flash.danger.not_personal_user_logged'
            );
            throw $this->createAccessDeniedException();
        }
        /** @var PersonalUser $user */
        $user = $this->getUser();
        $userWantsToBuy = (in_array($user->getOrientation(), [PersonalOrientationChoices::PERSONAL_ORIENTATION_BUY(), PersonalOrientationChoices::PERSONAL_ORIENTATION_BOTH()]))
            || ((bool)$request->get('vehicleReplace', false) === true);

        $userWantsToSell = (in_array($user->getOrientation(), [PersonalOrientationChoices::PERSONAL_ORIENTATION_SELL(), PersonalOrientationChoices::PERSONAL_ORIENTATION_BOTH()]))
            || (count($user->getVehicles()) > 0);

        if ($userWantsToBuy && $userWantsToSell) {
            $orientation = PersonalOrientationChoices::PERSONAL_ORIENTATION_BOTH();
        } elseif ($userWantsToSell) {
            $orientation = PersonalOrientationChoices::PERSONAL_ORIENTATION_SELL();
        } elseif ($userWantsToBuy) {
            $orientation = PersonalOrientationChoices::PERSONAL_ORIENTATION_BUY();
        } else {
            $orientation = null;
        }
        $personalOrientationForm = $this->formFactory->create(PersonalRegistrationOrientationType::class, ['orientation' => $orientation]);

        if ($this->session->has(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY) &&
            PersonalOrientationChoices::isValidKey($this->session->get(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY))) {
            // From landing mixte : orientation action is set in session => automatic validation of this step
            $inscQueryParam = $request->get(SecurityController::INSCRIPTION_QUERY_PARAM);
            if (!empty($inscQueryParam)) {
                $inscQueryParam = [SecurityController::INSCRIPTION_QUERY_PARAM => $inscQueryParam];
            } else {
                $inscQueryParam = [];
            }
            $orientation = new PersonalOrientationChoices($this->session->get(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY));
            $this->userEditionService->updateUserOrientation($user, $orientation);
            switch ($user->getOrientation()) {
                case PersonalOrientationChoices::PERSONAL_ORIENTATION_BOTH():
                case PersonalOrientationChoices::PERSONAL_ORIENTATION_SELL():
                    if (count($user->getVehicles()) == 0) {
                        // Only if no vehicle is already added (when registration with vehicle)
                        return $this->redirectToRoute('front_vehicle_personal_add', $inscQueryParam);
                    }
                case PersonalOrientationChoices::PERSONAL_ORIENTATION_BUY():
                    /* TYPEFORM désactivé : on redirige directement vers la page du particulier
                    return $this->redirectToRoute('front_affinity_personal_form', $inscQueryParam);
                    */
                    return $this->redirectToRoute('front_edit_user_project', $inscQueryParam);
                default:
                    $this->session->remove(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY);
                    $this->session->getFlashBag()->add(
                        self::FLASH_LEVEL_WARNING,
                        'flash.warning.personal_orientation.invalid_choice'
                    );
            }
        }
        $personalOrientationForm->handleRequest($request);
        if ($personalOrientationForm->isSubmitted() && $personalOrientationForm->isValid()) {
            $formData = $personalOrientationForm->getData();

            $this->userEditionService->updateUserOrientation($user, $formData['orientation']);
            $this->session->set(self::PERSONAL_ORIENTATION_ACTION_SESSION_KEY, $formData['orientation']->getValue());

            switch ($user->getOrientation()) {
                case PersonalOrientationChoices::PERSONAL_ORIENTATION_BOTH():
                case PersonalOrientationChoices::PERSONAL_ORIENTATION_SELL():
                    if (count($user->getVehicles()) == 0) {
                        // Only if no vehicle is already added (when registration with vehicle)
                        return $this->redirectToRoute('front_vehicle_personal_add');
                    }
                case PersonalOrientationChoices::PERSONAL_ORIENTATION_BUY():
                    /* TYPEFORM désactivé : on redirige directement vers la page du particulier
                    return $this->redirectToRoute('front_affinity_personal_form');
                    */
                    return $this->redirectToRoute('front_edit_user_project');
                default:
                    $this->session->getFlashBag()->add(
                        self::FLASH_LEVEL_WARNING,
                        'flash.warning.personal_orientation.invalid_choice'
                    );
            }
        }
        $this->session->remove(self::PERSONAL_ORIENTATION_ACTION_SESSION_KEY);

        return $this->render('front/Security/Register/orientation_personal_registration.html.twig', [
            'personalOrientationForm' => $personalOrientationForm->createView()
        ]);
    }
}
