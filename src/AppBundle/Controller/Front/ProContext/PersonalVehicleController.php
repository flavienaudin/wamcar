<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Controller\Front\PersonalContext\RegistrationController;
use AppBundle\Elasticsearch\Elastica\VehicleInfoEntityIndexer;
use AppBundle\Form\DTO\PersonalVehicleDTO;
use AppBundle\Form\Type\PersonalVehicleType;
use AppBundle\Services\User\UserEditionService;
use AppBundle\Services\Vehicle\PersonalVehicleEditionService;
use AppBundle\Session\SessionMessageManager;
use AutoData\ApiConnector;
use AutoData\Exception\AutodataException;
use AutoData\Exception\AutodataWithUserMessageException;
use AutoData\Request\GetInformationFromPlateNumber;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wamcar\User\PersonalUser;
use Wamcar\Vehicle\PersonalVehicle;

class PersonalVehicleController extends BaseController
{
    use VehicleTrait;

    /** @var FormFactoryInterface $formFactory */
    protected $formFactory;
    /** @var VehicleInfoEntityIndexer $vehicleInfoEntityIndexer */
    private $vehicleInfoEntityIndexer;
    /** @var PersonalVehicleEditionService $personalVehicleEditionService */
    private $personalVehicleEditionService;
    /** @var ApiConnector $autoDataConnector */
    protected $autoDataConnector;
    /** @var SessionMessageManager $sessionMessageManager */
    protected $sessionMessageManager;
    /** @var UserEditionService $userEditionService */
    protected $userEditionService;

    /**
     * GarageController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleInfoEntityIndexer $vehicleInfoEntityIndexer
     * @param PersonalVehicleEditionService $personalVehicleEditionService
     * @param ApiConnector $autoDataConnector
     * @param SessionMessageManager $sessionMessageManager
     * @param UserEditionService $userEditionService
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleInfoEntityIndexer $vehicleInfoEntityIndexer,
        PersonalVehicleEditionService $personalVehicleEditionService,
        ApiConnector $autoDataConnector,
        SessionMessageManager $sessionMessageManager,
        UserEditionService $userEditionService
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoEntityIndexer = $vehicleInfoEntityIndexer;
        $this->personalVehicleEditionService = $personalVehicleEditionService;
        $this->autoDataConnector = $autoDataConnector;
        $this->sessionMessageManager = $sessionMessageManager;
        $this->userEditionService = $userEditionService;
    }

    /**
     * @param Request $request
     * @param string $plateNumber
     * @param PersonalVehicle $vehicle
     * @Security("has_role('ROLE_USER')")
     * @return Response
     * @throws \AutoData\Exception\AutodataException
     * @throws \Exception
     */
    public function saveAction(
        Request $request,
        PersonalVehicle $vehicle = null,
        string $plateNumber = null): Response
    {

        if (!$this->getUser() instanceof PersonalUser) {
            throw new AccessDeniedException('Personal vehicle form is for personal');
        }

        if ($vehicle) {
            if (!$this->personalVehicleEditionService->canEdit($this->getUser(), $vehicle)) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_DANGER,
                    'flash.error.unauthorized_to_edit_vehicle'
                );
                return $this->redirectToRoute("front_default");
            }
            $vehicleDTO = PersonalVehicleDTO::buildFromPersonalVehicle($vehicle);
            if (!empty($plateNumber)) {
                $vehicleDTO->getVehicleRegistration()->setPlateNumber($plateNumber);
            }
            $actionRoute = $this->generateUrl('front_vehicle_personal_edit', [
                'id' => $vehicle->getId(),
                'plateNumber' => $plateNumber
            ]);
        } else {
            $vehicleDTO = new PersonalVehicleDTO($plateNumber);
            $vehicleDTO->setCity($this->getUser()->getCity());
            $actionRoute = $this->generateUrl('front_vehicle_personal_add', [
                'plateNumber' => $plateNumber
            ]);
        }

        $filters = $vehicleDTO->retrieveFilter();

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
                    if (isset($vehicleInfo['make']) && !empty($vehicleInfo['model'])) {
                        $filters['model'] = $vehicleInfo['model'];
                    } else {
                        $filters['model'] = $information['Vehicule']['MODELE_ETUDE'];
                    }
                    if (isset($vehicleInfo['make']) && !empty($vehicleInfo['engine'])) {
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
                if ($date1erCir) {
                    $vehicleDTO->setRegistrationDate($date1erCir);
                }
                $vin = $information['Vehicule']['CODIF_VIN_PRF'] ?? null;
                if ($vin) {
                    if (strlen($vin) < 17) {
                        $vin = str_pad($vin, 17, '_', STR_PAD_LEFT);
                    }
                    $vehicleDTO->setRegistrationVin($vin);
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

        $vehicleDTO->updateFromFilters($filters);

        $availableValues = $this->vehicleInfoEntityIndexer->getVehicleInfoAggregatesFromMakeAndModel($filters);

        $personalVehicleForm = $this->formFactory->create(
            PersonalVehicleType::class,
            $vehicleDTO,
            [
                'available_values' => $availableValues,
                'action' => $actionRoute
            ]);
        $personalVehicleForm->handleRequest($request);

        if ($personalVehicleForm->isSubmitted() && $personalVehicleForm->isValid()) {
            if ($vehicle) {
                $this->personalVehicleEditionService->updateInformations($vehicleDTO, $vehicle);
                $flashMessage = 'flash.success.vehicle_update';
            } else {
                $vehicle = $this->personalVehicleEditionService->createInformations($vehicleDTO, $this->getUser());
                $flashMessage = 'flash.success.vehicle_create';
            }
            /** @var PersonalUser $user */
            $user = $this->getUser();
            if ($user->getCity() === null || $user->getCity() != $vehicleDTO->getCity()) {
                $this->userEditionService->updateUserCity($user, $vehicleDTO->getCity());
            }

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                $flashMessage
            );

            if ($this->session->has(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY)) {
                // Post-registration assistant process in progress
                return $this->redirectToRoute('front_affinity_personal_form');
            }

            return $this->redirSave(
                ['v' => $vehicle->getId(), '_fragment' => 'message-answer-block'],
                'front_vehicle_personal_detail',
                ['slug' => $vehicle->getSlug()]
            );
        }

        return $this->render('front/Vehicle/Add/personal/add_personal.html.twig', [
            'personalVehicleForm' => $personalVehicleForm->createView(),
            'vehicle' => $vehicle
        ]);
    }

    /**
     * @param Request $request
     * @param PersonalVehicle $vehicle
     * @return Response
     */
    public function detailAction(Request $request, PersonalVehicle $vehicle): Response
    {
        $userLike = null;
        if ($this->isUserAuthenticated()) {
            $userLike = $vehicle->getLikeOfUser($this->getUser());
        }
        return $this->render('front/Vehicle/Detail/detail_personalVehicle.html.twig', [
            'isEditableByCurrentUser' => $this->personalVehicleEditionService->canEdit($this->getUser(), $vehicle),
            'vehicle' => $vehicle,
            'positiveLikes' => $vehicle->getPositiveLikesByUserType(),
            'like' => $userLike
        ]);
    }

    /**
     * @param Request $request
     * @param PersonalVehicle $vehicle
     * @return RedirectResponse
     */
    public function legacyDetailAction(Request $request, PersonalVehicle $vehicle): Response
    {
        return $this->redirectToRoute('front_vehicle_personal_detail', ['slug' => $vehicle->getSlug()], Response::HTTP_MOVED_PERMANENTLY);
    }
    /**
     * @param PersonalVehicle $personalVehicle
     * @return Response
     */
    public function deleteAction(PersonalVehicle $personalVehicle): Response
    {
        if (!$this->personalVehicleEditionService->canEdit($this->getUser(), $personalVehicle)) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_DANGER,
                'flash.error.remove_vehicle'
            );
            return $this->redirectToRoute('front_view_current_user_info', [
                'id' => $personalVehicle->getId()
            ]);
        }

        $this->personalVehicleEditionService->deleteVehicle($personalVehicle);

        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_INFO,
            'flash.success.remove_vehicle'
        );
        return $this->redirectToRoute('front_view_current_user_info');
    }


    /**
     * @param PersonalVehicle $vehicle
     * @param Request $request
     * @return Response
     */
    public function likePersonalVehicleAction(PersonalVehicle $vehicle, Request $request): Response
    {
        if (!$this->isUserAuthenticated()) {
            if ($request->headers->has("referer")) {
                $this->session->set(self::LIKE_REDIRECT_TO_SESSION_KEY, $request->headers->get('referer'));
            }
            throw new AccessDeniedException();
        }
        $this->personalVehicleEditionService->userLikesVehicle($this->getUser(), $vehicle);

        if ($this->session->has(self::LIKE_REDIRECT_TO_SESSION_KEY) || $request->headers->has("referer")) {
            $referer = $this->session->get(self::LIKE_REDIRECT_TO_SESSION_KEY, $request->headers->get("referer"));
            $this->session->remove(self::LIKE_REDIRECT_TO_SESSION_KEY);
            if (!empty($referer)) {
                if ($referer === $this->generateUrl('front_vehicle_personal_detail', ['slug' => $vehicle->getSlug()])) {
                    return $this->redirect($referer . '#header-' . $vehicle->getId());
                }
                return $this->redirect($referer . '#' . $vehicle->getId());
            }
        }
        return $this->redirectToRoute("front_vehicle_personal_detail", ['slug' => $vehicle->getSlug()]);
    }
}
