<?php

namespace AppBundle\Controller\Front;

use AppBundle\Controller\Front\ProContext\SearchController;
use AppBundle\Elasticsearch\Elastica\VehicleInfoEntityIndexer;
use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\DTO\VehicleInformationDTO;
use AppBundle\Form\Type\SearchVehicleType;
use AppBundle\Form\Type\VehicleInformationType;
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
    /** @var VehicleInfoEntityIndexer */
    private $vehicleInfoEntityIndexer;
    /** @var ProVehicleRepository $proVehicleRepository */
    private $proVehicleRepository;

    /**
     * DefaultController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleInfoEntityIndexer $vehicleInfoEntityIndexer
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleInfoEntityIndexer $vehicleInfoEntityIndexer,
        ProVehicleRepository $proVehicleRepository
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoEntityIndexer = $vehicleInfoEntityIndexer;
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
                'available_values' => $this->vehicleInfoEntityIndexer->getVehicleInfoAggregates(),
                'small_version' => true
            ]
        );

        $searchVehicleForm = $this->formFactory->create(
            SearchVehicleType::class,
            new SearchVehicleDTO(),
            [
                'action' => $this->generateRoute('front_search'),
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
                'action' => $this->generateRoute('front_search'),
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
                'action' => $this->generateRoute('front_search'),
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
