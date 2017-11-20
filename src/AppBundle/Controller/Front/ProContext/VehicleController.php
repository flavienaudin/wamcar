<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ApplicationGarage;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\DTO\ProVehicleDTO;
use AppBundle\Form\Type\GarageType;
use AppBundle\Form\Type\ProVehicleType;
use AppBundle\Services\Garage\GarageEditionService;
use AppBundle\Utils\VehicleInfoAggregator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Wamcar\Garage\Garage;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\Garage\GarageRepository;

class VehicleController extends BaseController
{
    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;

    /**
     * GarageController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleInfoAggregator $vehicleInfoAggregator
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
    }

    /**
     * @param Request $request
     * @Security("has_role('ROLE_PRO')")
     * @return RedirectResponse|Response
     */
    public function createAction(
        Request $request,
        array $filters = [],
        string $plateNumber = null): Response
    {
        $vehicleDTO = new ProVehicleDTO();

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
            //$garage = $this->garageEditionService->editInformations($garageDTO, $garage, $this->getUser());

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.vehicle_create'
            );
            return $this->redirectToRoute('front_vehicle_pro_add');
        }

        return $this->render('front/Vehicle/Add/add.html.twig', [
            'proVehicleForm' => $proVehicleForm->createView(),
        ]);
    }
}
