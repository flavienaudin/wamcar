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
            if(!$vehicle->canEditMe($this->getUser())){
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
                $this->personalVehicleEditionService->updateInformations($vehicleDTO, $vehicle);
            } else {
                $this->personalVehicleEditionService->createInformations($vehicleDTO, $this->getUser());
            }

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                $vehicle ? 'flash.success.vehicle_update' : 'flash.success.vehicle_create'
            );
            return $this->redirectToRoute('front_view_user_info', ['id' => $this->getUser()->getId()]);
        }

        return $this->render('front/Vehicle/Add/personal/add_personal.html.twig', [
            'personalVehicleForm' => $personalVehicleForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param ProVehicle $vehicle
     * @return Response
     */
    public function detailAction(Request $request, ProVehicle $vehicle): Response
    {

        return $this->render('front/Vehicle/Detail/detail.html.twig', [
            'isEditableByCurrentUser' => $this->personalVehicleEditionService->canEdit($this->getUser(), $vehicle),
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * @param ProVehicle $proVehicle
     * @return Response
     */
    public function deleteAction(ProVehicle $proVehicle): Response
    {
        if (!$this->personalVehicleEditionService->canEdit($this->getUser(), $proVehicle)) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_DANGER,
                'flash.error.remove_vehicle'
            );
            return $this->redirectToRoute('front_vehicle_pro_detail', [
                'id' => $proVehicle->getId()
            ]);
        }

        $this->personalVehicleEditionService->deleteVehicle($proVehicle);

        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_INFO,
            'flash.success.remove_vehicle'
        );

        return $this->redirectToRoute('front_garage_view', [
            'id' => $proVehicle->getGarage()->getId()
        ]);
    }
}
