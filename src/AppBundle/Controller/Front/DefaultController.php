<?php

namespace AppBundle\Controller\Front;

use AppBundle\Controller\Front\ProContext\SearchController;
use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\DTO\VehicleInformationDTO;
use AppBundle\Form\Type\SearchVehicleType;
use AppBundle\Form\Type\VehicleInformationType;
use AppBundle\Utils\VehicleInfoAggregator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\User\ProUser;
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
    public function landingRepriseAction(): Response
    {
        $vehicleInformationForm = $this->formFactory->create(
            VehicleInformationType::class,
            new VehicleInformationDTO(),
            [
                'available_values' => $this->vehicleInfoAggregator->getVehicleInfoAggregates(),
                'small_version' => true
            ]
        );

        $searchVehicleForm = $this->formFactory->create(
            SearchVehicleType::class,
            new SearchVehicleDTO(),
            [
                'action' => ($this->getUser() instanceof ProUser ?
                    $this->generateRoute('front_search', ['tab' => SearchController::TAB_PERSONAL]) :
                    $this->generateRoute('front_search', ['tab' => SearchCOntroller::TAB_PRO])),
                'small_version' => true
            ]
        );

        $last_vehicles = $this->proVehicleRepository->getLast(self::NB_PRO_VEHICLE_IN_HOMEPAGE);

        return $this->render(
            ':front/Home:home.html.twig',
            [
                'vehicleInformationForm' => $vehicleInformationForm->createView(),
                'smallSearchForm' => $searchVehicleForm->createView(),
                'last_vehicles' => $last_vehicles
            ]
        );
    }

    /**
     * @return Response
     */
    public function landingMeetingAction(): Response
    {
        $searchVehicleForm = $this->formFactory->create(
            SearchVehicleType::class,
            new SearchVehicleDTO(),
            [
                'action' => ($this->getUser() instanceof ProUser ?
                    $this->generateRoute('front_search', ['tab' => SearchController::TAB_PERSONAL]) :
                    $this->generateRoute('front_search', ['tab' => SearchCOntroller::TAB_PRO])),
                'small_version' => true
            ]
        );

        $last_vehicles = $this->proVehicleRepository->getLast(self::NB_PRO_VEHICLE_IN_HOMEPAGE);

        return $this->render(
            '/front/Home/landing_meeting.html.twig',
            [
                'smallSearchForm' => $searchVehicleForm->createView(),
                'last_vehicles' => $last_vehicles
            ]
        );
    }

    /**
     * @return Response
     */
    public function landingMixteAction(): Response
    {
        $searchVehicleForm = $this->formFactory->create(
            SearchVehicleType::class,
            new SearchVehicleDTO(),
            [
                'action' => ($this->getUser() instanceof ProUser ?
                    $this->generateRoute('front_search', ['tab' => SearchController::TAB_PERSONAL]) :
                    $this->generateRoute('front_search', ['tab' => SearchCOntroller::TAB_PRO])),
                'small_version' => true
            ]
        );

        $last_vehicles = $this->proVehicleRepository->getLast(self::NB_PRO_VEHICLE_IN_HOMEPAGE);

        return $this->render(
            '/front/Home/landing_mixte.html.twig',
            [
                'smallSearchForm' => $searchVehicleForm->createView(),
                'last_vehicles' => $last_vehicles
            ]
        );
    }
}
