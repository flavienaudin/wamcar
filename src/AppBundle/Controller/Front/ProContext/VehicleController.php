<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\ProVehicleDTO;
use AppBundle\Form\Type\ProVehicleType;
use AppBundle\Services\User\CanBeGarageMember;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use AppBundle\Utils\VehicleInfoAggregator;
use AutoData\ApiConnector;
use AutoData\Request\GetInformationFromPlateNumber;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Wamcar\Garage\Garage;
use Wamcar\Vehicle\ProVehicle;

class VehicleController extends BaseController
{
    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;
    /** @var ApiConnector */
    protected $autoDataConnector;

    /**
     * GarageController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     * @param ProVehicleEditionService $proVehicleEditionService
     * @param ApiConnector $autoDataConnector
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleInfoAggregator $vehicleInfoAggregator,
        ProVehicleEditionService $proVehicleEditionService,
        ApiConnector $autoDataConnector
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->proVehicleEditionService = $proVehicleEditionService;
        $this->autoDataConnector = $autoDataConnector;
    }

    /**
     * @param Request $request
     * @param string $plateNumber
     * @param ProVehicle $vehicle
     * @Security("has_role('ROLE_USER')")
     * @return Response
     * @throws \AutoData\Exception\AutodataException
     */
    public function saveAction(
        Request $request,
        ProVehicle $vehicle = null,
        string $plateNumber = null): Response
    {
        if (!$this->getUser() instanceof CanBeGarageMember || !$this->getUser()->getGarage()) {
            throw new AccessDeniedHttpException('You need to have an garage');
        }
        /** @var Garage $garage */
        $garage = $this->getUser()->getGarage();

        if ($vehicle) {
            $vehicleDTO = ProVehicleDTO::buildFromProVehicle($vehicle);
        } else {
            $vehicleDTO = new ProVehicleDTO($plateNumber);
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

        $proVehicleForm = $this->formFactory->create(
            ProVehicleType::class,
            $vehicleDTO,
            ['available_values' => $availableValues]);
        $proVehicleForm->handleRequest($request);

        if ($proVehicleForm->isSubmitted() && $proVehicleForm->isValid()) {
            if ($vehicle) {
                $this->proVehicleEditionService->updateInformations($vehicleDTO, $vehicle);
            } else {
                $this->proVehicleEditionService->createInformations($vehicleDTO, $garage);
            }

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                $vehicle ? 'flash.success.vehicle_update' : 'flash.success.vehicle_create'
            );
            return $this->redirectToRoute('front_garage_view', ['id' => $garage->getId()]);
        }

        return $this->render('front/Vehicle/Add/add.html.twig', [
            'proVehicleForm' => $proVehicleForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param ProVehicle $vehicle
     * @return Response
     */
    public function detailAction(Request $request, ProVehicle $vehicle): Response
    {
        return $this->render('front/Vehicle/Detail/detail_proVehicle.html.twig', [
            'isEditableByCurrentUser' => $this->proVehicleEditionService->canEdit($this->getUser(), $vehicle),
            'vehicle' => $vehicle,
            'isProVehicle' => true
        ]);
    }

    /**
     * @param ProVehicle $proVehicle
     * @return Response
     */
    public function deleteAction(ProVehicle $proVehicle): Response
    {
        if (!$this->proVehicleEditionService->canEdit($this->getUser(), $proVehicle)) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_DANGER,
                'flash.error.remove_vehicle'
            );
            return $this->redirectToRoute('front_vehicle_pro_detail', [
                'id' => $proVehicle->getId()
            ]);
        }

        $this->proVehicleEditionService->deleteVehicle($proVehicle);

        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_INFO,
            'flash.success.remove_vehicle'
        );

        return $this->redirectToRoute('front_garage_view', [
            'id' => $proVehicle->getGarage()->getId()
        ]);
    }
}
