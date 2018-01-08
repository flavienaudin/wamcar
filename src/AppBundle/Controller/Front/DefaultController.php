<?php

namespace AppBundle\Controller\Front;

use AppBundle\Form\DTO\VehicleInformationDTO;
use AppBundle\Form\Type\VehicleInformationType;
use AppBundle\Utils\VehicleInfoAggregator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\Vehicle\ProVehicleRepository;

class DefaultController extends BaseController
{
    /** Number of pro vehicles to display in the homepage */
    const NB_PRO_VEHICLE_IN_HOMEPAGE = 6;

    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;
    /** @var ProVehicleRepository $proVehicleRepository */
    private $proVehicleRepository;

    /**
     * DefaultController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleInfoAggregator $vehicleInfoAggregator,
        ProVehicleRepository $proVehicleRepository
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->proVehicleRepository = $proVehicleRepository;
    }

    /**
     * @return Response
     */
    public function homepageAction(): Response
    {
        if ($this->isUserAuthenticated()) {
            return $this->redirectToRoute('front_view_current_user_info');
        }

        $vehicleInformationDTO = new VehicleInformationDTO();

        $vehicleInformationForm = $this->formFactory->create(
            VehicleInformationType::class,
            $vehicleInformationDTO,
            [
                'available_values' => $this->vehicleInfoAggregator->getVehicleInfoAggregates(),
                'small_version' => true
            ]
        );

        $last_vehicles = $this->proVehicleRepository->getLast(self::NB_PRO_VEHICLE_IN_HOMEPAGE);

        return $this->render(
            ':front/Home:home.html.twig',
            [
                'vehicleInformationForm' => $vehicleInformationForm->createView(),
                'last_vehicles' => $last_vehicles
            ]
        );
    }
}
