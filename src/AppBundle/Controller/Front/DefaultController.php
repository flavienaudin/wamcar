<?php

namespace AppBundle\Controller\Front;

use AppBundle\Form\DTO\VehicleIdentificationDTO;
use AppBundle\Form\Type\VehicleIdentificationType;
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
        $vehicleIdentificationDTO = new VehicleIdentificationDTO();

        $vehicleIdentificationForm = $this->formFactory->create(
            VehicleIdentificationType::class,
            $vehicleIdentificationDTO,
            [
                'available_values' => [],
                'available_values' => $this->vehicleInfoAggregator->getVehicleInfoAggregates(),
                'small_version' => true
            ]
        );

        //        $vehicleForm->handleRequest($request);
        //        dump($vehicleForm->get('specifics'));
        //
        //        if ($vehicleForm->isSubmitted() && $vehicleForm->isValid()) {
        //            $personalVehicle = PersonalVehicleBuilder::buildFromDTO($vehicleDTO);
        //            $this->vehicleRepository->add($personalVehicle);
        //
        //            dump("Picture saved");
        //            dump($personalVehicle);
        //            exit;
        //        }


        return $this->render(
            ':front/Home:home.html.twig',
            [
                'vehicleIdentificationForm' => $vehicleIdentificationForm->createView(),
            ]
        );
    }
}
