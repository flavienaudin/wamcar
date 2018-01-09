<?php

namespace AppBundle\Controller\Front\PersonalContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\UserRegistrationPersonalVehicleDTO;
use AppBundle\Form\EntityBuilder\PersonalVehicleBuilder;
use AppBundle\Form\Type\UserRegistrationPersonalVehicleType;
use AppBundle\Security\UserRegistrationService;
use AppBundle\Utils\VehicleInfoAggregator;
use AutoData\ApiConnector;
use AutoData\Exception\AutodataException;
use AutoData\Exception\AutodataWithUserMessageException;
use AutoData\Request\GetInformationFromPlateNumber;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PragmaRX\ZipCode\ZipCode;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\User\PersonalUser;
use Wamcar\Vehicle\Event\PersonalVehicleCreated;
use Wamcar\Vehicle\PersonalVehicleRepository;

class RegistrationController extends BaseController
{
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var PersonalVehicleRepository */
    private $vehicleRepository;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;
    /** @var UserRegistrationService */
    protected $userRegistrationService;
    /** @var ApiConnector */
    protected $autoDataConnector;
    /** @var ZipCode */
    protected $zipCodeService;
    /** @var MessageBus */
    protected $eventBus;

    /**
     * RegistrationController constructor.
     * @param FormFactoryInterface $formFactory
     * @param PersonalVehicleRepository $vehicleRepository
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     * @param UserRegistrationService $userRegistrationService
     * @param ApiConnector $autoDataConnector
     * @param ZipCode $zipCodeService
     * @param MessageBus $eventBus
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        PersonalVehicleRepository $vehicleRepository,
        VehicleInfoAggregator $vehicleInfoAggregator,
        UserRegistrationService $userRegistrationService,
        ApiConnector $autoDataConnector,
        ZipCode $zipCodeService,
        MessageBus $eventBus
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleRepository = $vehicleRepository;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->userRegistrationService = $userRegistrationService;
        $this->autoDataConnector = $autoDataConnector;
        $this->zipCodeService = $zipCodeService;
        $this->eventBus = $eventBus;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function vehicleRegistrationAction(Request $request): Response
    {
        $filters = $request->get('vehicle_information', []);
        unset($filters['_token']);

        if ($plateNumber = $request->get('plate_number', null)) {
            try {
                $information = $this->autoDataConnector->executeRequest(new GetInformationFromPlateNumber($plateNumber));
                $ktypNumber = $information['Vehicule']['LTYPVEH']['TYPVEH']['KTYPNR'] ?? null;
                $filters = $ktypNumber ? ['ktypNumber' => $ktypNumber] : $filters;
            } catch (AutodataException $autodataException) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_DANGER,
                    $autodataException instanceof AutodataWithUserMessageException ?
                        $autodataException->getMessage() :
                        'flash.warning.registration_recognition_failed'
                );
            }
        }

        return $this->vehicleRegistrationFromInformation($request, $filters, $plateNumber);
    }

    /**
     * @param Request $request
     * @param array $filters
     * @param string|null $plateNumber
     * @return Response
     * @throws \Exception
     */
    private function vehicleRegistrationFromInformation(
        Request $request,
        array $filters = [],
        string $plateNumber = null): Response
    {
        $vehicleDTO = new UserRegistrationPersonalVehicleDTO($plateNumber);
        $vehicleDTO->updateFromFilters($filters);

        $availableValues = array_key_exists('ktypNumber', $filters) ?
            $this->vehicleInfoAggregator->getVehicleInfoAggregates($filters) :
            $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel($filters);

        $vehicleForm = $this->formFactory->create(
            UserRegistrationPersonalVehicleType::class,
            $vehicleDTO,
            ['available_values' => $availableValues]);

        $vehicleForm->handleRequest($request);

        if ($vehicleForm->isSubmitted() && $vehicleForm->isValid()) {
            $personalVehicle = PersonalVehicleBuilder::buildFromDTO($vehicleDTO);
            $this->vehicleRepository->add($personalVehicle);

            try {
                $applicationUser = $this->userRegistrationService->registerUser($vehicleDTO->userRegistration);
                if($applicationUser instanceof PersonalUser){
                    $personalVehicle->setOwner($applicationUser);
                    $this->vehicleRepository->update($personalVehicle);
                }
                $this->eventBus->handle(new PersonalVehicleCreated($personalVehicle));
            } catch (UniqueConstraintViolationException $exception) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_DANGER,
                    'flash.danger.registration_duplicate'
                );
            }

            return $this->redirectToRoute('register_confirm');
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCityByZipcodeAction(Request $request): JsonResponse
    {
        $zipcode = $request->get('zipcode', null);
        $city = $this->zipCodeService->find($zipcode);

        return new JsonResponse($city->toArray());
    }
}
