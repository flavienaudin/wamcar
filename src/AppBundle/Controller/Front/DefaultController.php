<?php

namespace AppBundle\Controller\Front;

use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\DTO\VehicleInformationDTO;
use AppBundle\Form\Type\SearchVehicleType;
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
        $searchVehicleDTO = new SearchVehicleDTO();

        $vehicleInformationForm = $this->formFactory->create(
            VehicleInformationType::class,
            $vehicleInformationDTO,
            [
                'available_values' => $this->vehicleInfoAggregator->getVehicleInfoAggregates(),
                'small_version' => true
            ]
        );

        $searchVehicleDTO = $this->formFactory->create(
            SearchVehicleType::class,
            $searchVehicleDTO,
            [
                'method' => 'GET',
                'action' => $this->generateRoute('front_search_personal'),
                'small_version' => true,
                'available_values' => []
            ]
        );

        $last_vehicles = $this->proVehicleRepository->getLast(self::NB_PRO_VEHICLE_IN_HOMEPAGE);

        return $this->render(
            ':front/Home:home.html.twig',
            [
                'vehicleInformationForm' => $vehicleInformationForm->createView(),
                'smallSearchForm' => $searchVehicleDTO->createView(),
                'last_vehicles' => $last_vehicles
            ]
        );
    }
}
