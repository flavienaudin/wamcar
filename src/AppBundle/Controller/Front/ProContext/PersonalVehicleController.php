<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\PersonalVehicleDTO;
use AppBundle\Form\Type\PersonalVehicleType;
use AppBundle\Services\Vehicle\PersonalVehicleEditionService;
use AppBundle\Utils\VehicleInfoAggregator;
use AutoData\ApiConnector;
use AutoData\Request\GetInformationFromPlateNumber;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Wamcar\User\PersonalUser;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\ProVehicle;

class PersonalVehicleController extends BaseController
{
    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;
    /** @var PersonalVehicleEditionService */
    private $personalVehicleEditionService;
    /** @var ApiConnector */
    protected $autoDataConnector;


    /**
     * GarageController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     * @param PersonalVehicleEditionService $personalVehicleEditionService
     * @param ApiConnector $autoDataConnector
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleInfoAggregator $vehicleInfoAggregator,
        PersonalVehicleEditionService $personalVehicleEditionService,
        ApiConnector $autoDataConnector
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->personalVehicleEditionService = $personalVehicleEditionService;
        $this->autoDataConnector = $autoDataConnector;
    }

    /**
     * @param Request $request
     * @param string $plateNumber
     * @param PersonalVehicle $vehicle
     * @Security("has_role('ROLE_USER')")
     * @return Response
     * @throws \AutoData\Exception\AutodataException
     */
    public function saveAction(
        Request $request,
        PersonalVehicle $vehicle = null,
        string $plateNumber = null): Response
    {

        if (!$this->getUser() instanceof PersonalUser) {
            throw new AccessDeniedHttpException('Pro user need a garage');
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
        } else {
            $vehicleDTO = new PersonalVehicleDTO($plateNumber);
        }

        $filters = $vehicleDTO->retrieveFilter();

        if ($plateNumber) {
            $information = $this->autoDataConnector->executeRequest(new GetInformationFromPlateNumber($plateNumber));
            $ktypNumber = $information['Vehicule']['LTYPVEH']['TYPVEH']['KTYPNR'] ?? null;
            $filters = $ktypNumber ? ['ktypNumber' => $ktypNumber] : [];
        }

        $vehicleDTO->updateFromFilters($filters);

        $availableValues = array_key_exists('ktypNumber', $filters) ?
            $this->vehicleInfoAggregator->getVehicleInfoAggregates($filters) :
            $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel($filters);

        $personalVehicleForm = $this->formFactory->create(
            PersonalVehicleType::class,
            $vehicleDTO,
            ['available_values' => $availableValues]);
        $personalVehicleForm->handleRequest($request);

        if ($personalVehicleForm->isSubmitted() && $personalVehicleForm->isValid()) {
            if ($vehicle) {
                $this->personalehicleEditionService->updateInformations($vehicleDTO, $vehicle);
                $flashMessage = 'flash.success.vehicle_update';
            } else {
                $vehicle = $this->personalVehicleEditionService->createInformations($vehicleDTO, $this->getUser());
                $flashMessage = 'flash.success.vehicle_create';
            }

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                $flashMessage
            );
            return $this->redirectToRoute('front_vehicle_personal_detail', ['id' => $vehicle->getId()]);
        }

        return $this->render('front/Vehicle/Add/personal/add_personal.html.twig', [
            'personalVehicleForm' => $personalVehicleForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param PersonalVehicle $vehicle
     * @return Response
     */
    public function detailAction(Request $request, PersonalVehicle $vehicle): Response
    {
        if(!$vehicle->canSeeMe($this->getUser())){
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_DANGER,
                'flash.error.unauthorized_to_see_vehicle'
            );
            return $this->redirectToRoute("front_default");
        }
        return $this->render('front/Vehicle/Detail/detail_personalVehicle.html.twig', [
            'isEditableByCurrentUser' => $this->personalVehicleEditionService->canEdit($this->getUser(), $vehicle),
            'vehicle' => $vehicle,
            'isProVehicle' => false
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

        // TODO redirectTo personal_user_detail
        return $this->redirectToRoute('front_view_current_user_info');
    }
}
