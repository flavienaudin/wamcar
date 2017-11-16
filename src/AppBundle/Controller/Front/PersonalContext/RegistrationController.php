<?php

namespace AppBundle\Controller\Front\PersonalContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\VehicleDTO;
use AppBundle\Form\EntityBuilder\PersonalVehicleBuilder;
use AppBundle\Form\Type\VehicleType;
use AppBundle\Security\UserRegistrationService;
use AppBundle\Utils\VehicleInfoAggregator;
use AutoData\ApiConnector;
use AutoData\Exception\AutodataException;
use AutoData\Exception\AutodataWithUserMessageException;
use AutoData\Request\GetInformationFromPlateNumber;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PragmaRX\ZipCode\ZipCode;
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
    /** @var UserRegistrationService */
    protected $userRegistrationService;
    /** @var ApiConnector */
    protected $autoDataConnector;
    /** @var ZipCode */
    protected $zipCodeService;

    /**
     * RegistrationController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleRepository $vehicleRepository
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     * @param UserRegistrationService $userRegistrationService
     * @param ApiConnector $autoDataConnector
     * @param ZipCode $zipCodeService
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleRepository $vehicleRepository,
        VehicleInfoAggregator $vehicleInfoAggregator,
        UserRegistrationService $userRegistrationService,
        ApiConnector $autoDataConnector,
        ZipCode $zipCodeService
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleRepository = $vehicleRepository;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->userRegistrationService = $userRegistrationService;
        $this->autoDataConnector = $autoDataConnector;
        $this->zipCodeService = $zipCodeService;
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
                    $autodataException instanceof AutodataWithUserMessageException ?
                        $autodataException->getMessage() :
                        self::FLASH_LEVEL_DANGER,
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
        $vehicleDTO = new VehicleDTO($plateNumber);
        $vehicleDTO->updateFromFilters($filters);

        $availableValues = array_key_exists('ktypNumber', $filters) ?
            $this->vehicleInfoAggregator->getVehicleInfoAggregates($filters) :
            $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel($filters);

        $vehicleForm = $this->formFactory->create(
            VehicleType::class,
            $vehicleDTO,
            ['available_values' => $availableValues]);

        $vehicleForm->handleRequest($request);

        if ($vehicleForm->isSubmitted() && $vehicleForm->isValid()) {
            $personalVehicle = PersonalVehicleBuilder::buildFromDTO($vehicleDTO);
            $this->vehicleRepository->add($personalVehicle);

            try {
                $this->userRegistrationService->registerUser($vehicleDTO->userRegistration);
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
