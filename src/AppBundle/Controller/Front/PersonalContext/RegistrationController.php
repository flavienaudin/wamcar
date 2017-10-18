<?php

namespace AppBundle\Controller\Front\PersonalContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\VehicleDTO;
use AppBundle\Form\EntityBuilder\PersonalVehicleBuilder;
use AppBundle\Form\Type\VehicleType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\Vehicle\VehicleRepository;

class RegistrationController extends BaseController
{
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var VehicleRepository */
    private $vehicleRepository;

    /**
     * RegistrationController constructor.
     */
    public function __construct(FormFactoryInterface $formFactory, VehicleRepository $vehicleRepository)
    {
        $this->formFactory = $formFactory;
        $this->vehicleRepository = $vehicleRepository;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function vehicleRegistrationAction(Request $request): Response
    {
        $vehicleDTO = new VehicleDTO();
        $vehicleForm = $this->formFactory->create(VehicleType::class, $vehicleDTO);

        $vehicleForm->handleRequest($request);
        dump($vehicleForm->get('specifics'));

        if ($vehicleForm->isSubmitted() && $vehicleForm->isValid()) {
            $personalVehicle = PersonalVehicleBuilder::buildFromDTO($vehicleDTO);
            $this->vehicleRepository->add($personalVehicle);

            dump("Picture saved");
            dump($personalVehicle);
            exit;
        }


        return $this->render(
            ':front/personalContext/registration:vehicle_registration.html.twig',
            [
                'vehicleForm' => $vehicleForm->createView(),
            ]
        );
    }
}
