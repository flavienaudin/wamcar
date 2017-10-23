<?php

namespace AppBundle\Controller\Front\PersonalContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\VehicleDTO;
use AppBundle\Form\EntityBuilder\PersonalVehicleBuilder;
use AppBundle\Form\Type\VehicleType;
use AppBundle\Utils\VehicleInfoAggregator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\Vehicle\VehicleRepository;

class RegistrationController extends BaseController
{
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var VehicleRepository */
    private $vehicleRepository;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;

    /**
     * RegistrationController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleRepository $vehicleRepository
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleRepository $vehicleRepository,
        VehicleInfoAggregator $vehicleInfoAggregator
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleRepository = $vehicleRepository;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function vehicleRegistrationAction(Request $request): Response
    {
        $vehicleDTO = new VehicleDTO();
        $vehicleForm = $this->formFactory->create(
            VehicleType::class,
            $vehicleDTO,
            ['available_values' => $this->vehicleInfoAggregator->getVehicleInfoAggregates()]
        );

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

    /**
     * @param Request $request
     * @return Response
     */
    public function updateVehicleRegistrationFormAction(Request $request): JsonResponse
    {
        $filters = $request->get('filters', []);
        $fetch = $request->get('fetch', null);

        $aggregates = $this->vehicleInfoAggregator->getVehicleInfoAggregates($filters);

        return new JsonResponse($fetch ? $aggregates[$fetch] : $aggregates);
    }
}
