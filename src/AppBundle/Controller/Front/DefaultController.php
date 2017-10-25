<?php

namespace AppBundle\Controller\Front;

use AppBundle\Form\DTO\VehicleInformationDTO;
use AppBundle\Form\Type\VehicleInformationType;
use AppBundle\Utils\VehicleInfoAggregator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends BaseController
{
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;

    /**
     * DefaultController constructor.
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
     * @return Response
     */
    public function homepageAction(): Response
    {
        $vehicleInformationDTO = new VehicleInformationDTO();

        $vehicleInformationForm = $this->formFactory->create(
            VehicleInformationType::class,
            $vehicleInformationDTO,
            [
                'available_values' => [],
                'available_values' => $this->vehicleInfoAggregator->getVehicleInfoAggregates(),
                'small_version' => true
            ]
        );

        return $this->render(
            ':front/Home:home.html.twig',
            [
                'vehicleInformationForm' => $vehicleInformationForm->createView(),
            ]
        );
    }
}
