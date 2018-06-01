<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\PersonalVehicleDTO;
use AppBundle\Form\Type\PersonalVehicleType;
use AppBundle\Services\Vehicle\PersonalVehicleEditionService;
use AppBundle\Session\SessionMessageManager;
use AppBundle\Utils\VehicleInfoAggregator;
use AutoData\ApiConnector;
use AutoData\Exception\AutodataException;
use AutoData\Exception\AutodataWithUserMessageException;
use AutoData\Request\GetInformationFromPlateNumber;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wamcar\User\PersonalUser;
use Wamcar\Vehicle\PersonalVehicle;

class PersonalVehicleController extends BaseController
{
    use VehicleTrait;

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;
    /** @var PersonalVehicleEditionService */
    private $personalVehicleEditionService;
    /** @var ApiConnector */
    protected $autoDataConnector;
    /** @var SessionMessageManager */
    protected $sessionMessageManager;


    /**
     * GarageController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     * @param PersonalVehicleEditionService $personalVehicleEditionService
     * @param ApiConnector $autoDataConnector
     * @param SessionMessageManager $sessionMessageManager
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleInfoAggregator $vehicleInfoAggregator,
        PersonalVehicleEditionService $personalVehicleEditionService,
        ApiConnector $autoDataConnector,
        SessionMessageManager $sessionMessageManager
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->personalVehicleEditionService = $personalVehicleEditionService;
        $this->autoDataConnector = $autoDataConnector;
        $this->sessionMessageManager = $sessionMessageManager;
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
            throw new AccessDeniedException('Pro user need a garage');
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
        } else {
            $vehicleDTO = new PersonalVehicleDTO($plateNumber);
        }

        $filters = $vehicleDTO->retrieveFilter();

        if ($plateNumber) {
            try {
                $information = $this->autoDataConnector->executeRequest(new GetInformationFromPlateNumber($plateNumber));
                $ktypNumber = $information['Vehicule']['LTYPVEH']['TYPVEH']['KTYPNR'] ?? null;
                $filters = $ktypNumber ? ['ktypNumber' => $ktypNumber] : [];
                $filters['make'] = $information['Vehicule']['MARQUE'];
                $filters['model'] = $information['Vehicule']['MODELE_ETUDE'];
                $filters['engine'] = $information['Vehicule']['VERSION'];
                $date1erCir = $information['Vehicule']['DATE_1ER_CIR'] ?? null;
                if ($date1erCir) {
                    $vehicleDTO->setRegistrationDate($date1erCir);
                }
                $vin = $information['Vehicule']['CODIF_VIN_PRF'] ?? null;
                if ($vin) {
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

        $availableValues = $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel($filters);

        $personalVehicleForm = $this->formFactory->create(
            PersonalVehicleType::class,
            $vehicleDTO,
            ['available_values' => $availableValues]);
        $personalVehicleForm->handleRequest($request);

        if ($personalVehicleForm->isSubmitted() && $personalVehicleForm->isValid()) {
            if ($vehicle) {
                $this->personalVehicleEditionService->updateInformations($vehicleDTO, $vehicle);
                $flashMessage = 'flash.success.vehicle_update';
            } else {
                $vehicle = $this->personalVehicleEditionService->createInformations($vehicleDTO, $this->getUser());
                $flashMessage = 'flash.success.vehicle_create';
            }

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                $flashMessage
            );

            return $this->redirSave($vehicle, 'front_vehicle_personal_detail');
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
                if ($referer === $this->generateUrl('front_vehicle_personal_detail', ['id' => $vehicle->getId()])) {
                    return $this->redirect($referer . '#header-' . $vehicle->getId());
                }
                return $this->redirect($referer . '#' . $vehicle->getId());
            }
        }
        return $this->redirectToRoute("front_vehicle_personal_detail", ['id' => $vehicle->getId()]);
    }
}
