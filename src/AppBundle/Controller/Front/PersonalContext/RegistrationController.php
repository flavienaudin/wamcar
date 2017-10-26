<?php

namespace AppBundle\Controller\Front\PersonalContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\VehicleDTO;
use AppBundle\Form\EntityBuilder\PersonalVehicleBuilder;
use AppBundle\Form\Type\VehicleType;
use AppBundle\Security\UserRegistrationService;
use AppBundle\Utils\VehicleInfoAggregator;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
    /** @var UserRegistrationService  */
    protected $userRegistrationService;

    /**
     * RegistrationController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleRepository $vehicleRepository
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     * @param UserRegistrationService $userRegistrationService
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleRepository $vehicleRepository,
        VehicleInfoAggregator $vehicleInfoAggregator,
        UserRegistrationService $userRegistrationService
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleRepository = $vehicleRepository;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->userRegistrationService = $userRegistrationService;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function vehicleRegistrationAction(Request $request): Response
    {
        $filters = $request->get('vehicle_information', []);
        unset($filters['_token']);

        $vehicleDTO = new VehicleDTO();
        $vehicleDTO->updateFromFilters($filters);

        $vehicleForm = $this->formFactory->create(
            VehicleType::class,
            $vehicleDTO,
            ['available_values' => $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel($filters)]
        );

        $vehicleForm->handleRequest($request);

        if ($vehicleForm->isSubmitted() && $vehicleForm->isValid()) {
            $personalVehicle = PersonalVehicleBuilder::buildFromDTO($vehicleDTO);
            $this->vehicleRepository->add($personalVehicle);

            try {
                $this->userRegistrationService->registerUser($vehicleDTO->userRegistration);
            } catch (UniqueConstraintViolationException $exception) {
                $this->session->getFlashBag()->add(
                    'flash.danger.registration_duplicate',
                    self::FLASH_LEVEL_DANGER
                );
            }

            die('OK');
        }


        return $this->render(
            ':front/Security/Register:user_car.html.twig',
            [
                'vehicleForm' => $vehicleForm->createView(),
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateVehicleRegistrationFormAction(Request $request): JsonResponse
    {
        $filters = $request->get('filters', []);
        $fetch = $request->get('fetch', null);

        $aggregates = $this->vehicleInfoAggregator->getVehicleInfoAggregates($filters);

        return new JsonResponse($fetch ? $aggregates[$fetch] : $aggregates);
    }
}
