<?php

namespace AppBundle\Controller\Front\PersonalContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\VehicleDTO;
use AppBundle\Form\Type\VehicleType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends BaseController
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /**
     * RegistrationController constructor.
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
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

        if ($vehicleForm->isSubmitted() && $vehicleForm->isValid()) {
            dump($vehicleDTO);

        }

        return $this->render(
            ':front/personalContext/registration:vehicle_registration.html.twig',
            [
                'vehicleForm' => $vehicleForm->createView(),
            ]
        );
    }
}
