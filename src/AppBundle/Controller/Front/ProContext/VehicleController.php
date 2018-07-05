<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Controller\Front\SecurityController;
use AppBundle\Form\DTO\ProVehicleDTO;
use AppBundle\Form\Type\ProVehicleType;
use AppBundle\Services\User\CanBeGarageMember;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use AppBundle\Session\SessionMessageManager;
use AppBundle\Utils\VehicleInfoAggregator;
use AppBundle\Utils\VehicleInfoProvider;
use AutoData\ApiConnector;
use AutoData\Exception\AutodataException;
use AutoData\Exception\AutodataWithUserMessageException;
use AutoData\Request\GetInformationFromPlateNumber;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wamcar\Garage\Garage;
use Wamcar\Vehicle\ProVehicle;

class VehicleController extends BaseController
{
    use VehicleTrait;

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;
    /** @var VehicleInfoProvider */
    private $vehicleInfoProvider;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;
    /** @var ApiConnector */
    protected $autoDataConnector;
    /** @var SessionMessageManager */
    protected $sessionMessageManager;

    /**
     * GarageController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     * @param VehicleInfoProvider $vehicleInfoProvider
     * @param ProVehicleEditionService $proVehicleEditionService
     * @param ApiConnector $autoDataConnector
     * @param SessionMessageManager $sessionMessageManager
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleInfoAggregator $vehicleInfoAggregator,
        VehicleInfoProvider $vehicleInfoProvider,
        ProVehicleEditionService $proVehicleEditionService,
        ApiConnector $autoDataConnector,
        SessionMessageManager $sessionMessageManager
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->vehicleInfoProvider = $vehicleInfoProvider;
        $this->proVehicleEditionService = $proVehicleEditionService;
        $this->autoDataConnector = $autoDataConnector;
        $this->sessionMessageManager = $sessionMessageManager;
    }

    /**
     * @param Request $request
     * @param string $plateNumber
     * @param ProVehicle $vehicle
     * @Security("has_role('ROLE_USER')")
     * @return Response
     * @throws \AutoData\Exception\AutodataException
     */
    public function saveAction(
        Request $request,
        ProVehicle $vehicle = null,
        string $plateNumber = null): Response
    {
        if (!$this->getUser() instanceof CanBeGarageMember || !$this->getUser()->getGarage()) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.pro_user_need_garage');
            return $this->redirectToRoute("front_garage_create");
        }
        /** @var Garage $garage */
        $garage = $this->getUser()->getGarage();

        if ($vehicle) {
            if (!$this->proVehicleEditionService->canEdit($this->getUser(), $vehicle)) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_DANGER,
                    'flash.error.unauthorized_to_edit_vehicle'
                );
                return $this->redirectToRoute("front_garage_view", ['id' => $garage->getId()]);
            }
            $vehicleDTO = ProVehicleDTO::buildFromProVehicle($vehicle);
            if (!empty($plateNumber)) {
                $vehicleDTO->getVehicleRegistration()->setPlateNumber($plateNumber);
            }
            $actionRoute = $this->generateUrl('front_vehicle_pro_edit', [
                'id' => $vehicle->getId(),
                'plateNumber' => $plateNumber
            ]);
        } else {
            $vehicleDTO = new ProVehicleDTO($plateNumber);
            $vehicleDTO->setCity($garage->getCity());
            $actionRoute = $this->generateUrl('front_vehicle_pro_add', [
                'plateNumber' => $plateNumber
            ]);
        }

        $filters = $vehicleDTO->retrieveFilter();

        if ($plateNumber) {
            try {
                $information = $this->autoDataConnector->executeRequest(new GetInformationFromPlateNumber($plateNumber));
                $ktypNumber = null;
                if (isset($information['Vehicule']['ktypnr_aaa']) && !empty($information['Vehicule']['ktypnr_aaa'])) {
                    $ktypNumber = $information['Vehicule']['ktypnr_aaa'];
                } elseif (isset($information['Vehicule']['LTYPVEH'])) {
                    foreach ($information['Vehicule']['LTYPVEH'] as $key => $value) {
                        if (isset($value['KTYPNR']) && !empty($value['KTYPNR'])) {
                            if (!empty($ktypNumber)) {
                                $this->session->getFlashBag()->add(
                                    self::FLASH_LEVEL_DANGER,
                                    'flash.warning.vehicle.multiple_vehicle_types'
                                );
                            }
                            $ktypNumber = $value['KTYPNR'];
                        }
                    }
                };

                $vehicleInfo = [];
                if (!empty($ktypNumber)) {
                    $vehicleInfo = $this->vehicleInfoProvider->getVehicleInfoByKtypNumber($ktypNumber);
                }
                if (count($vehicleInfo) == 1) {
                    if (isset($vehicleInfo[0]['make']) && !empty($vehicleInfo[0]['make'])) {
                        $filters['make'] = $vehicleInfo[0]['make'];
                    } else {
                        $filters['make'] = $information['Vehicule']['MARQUE'];
                    }
                    if (isset($vehicleInfo[0]['make']) && !empty($vehicleInfo[0]['model'])) {
                        $filters['model'] = $vehicleInfo[0]['model'];
                    } else {
                        $filters['model'] = $information['Vehicule']['MODELE_ETUDE'];
                    }
                    if (isset($vehicleInfo[0]['make']) && !empty($vehicleInfo[0]['engine'])) {
                        $filters['engine'] = $vehicleInfo[0]['engine'];
                    } else {
                        $filters['engine'] = $information['Vehicule']['VERSION'];
                    }

                } else {
                    $filters['make'] = $information['Vehicule']['MARQUE'];
                    $filters['model'] = $information['Vehicule']['MODELE_ETUDE'];
                    $filters['engine'] = $information['Vehicule']['VERSION'];
                }

                $date1erCir = $information['Vehicule']['DATE_1ER_CIR'] ?? null;
                if ($date1erCir) {
                    $vehicleDTO->setRegistrationDate($date1erCir);
                }
                $vin = $information['Vehicule']['CODIF_VIN_PRF'] ?? null;
                if ($vin) {
                    if (strlen($vin) < 17) {
                        $vin = str_pad($vin, 17, '_', STR_PAD_LEFT);
                    }
                    $vehicleDTO->setRegistrationVin($vin);
                }
            } catch (AutodataException $autodataException) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_DANGER,
                    $autodataException instanceof AutodataWithUserMessageException ?
                        $autodataException->getMessage() :
                        'flash.warning.registration_recognition_failed'
                );
            }
        }

        $vehicleDTO->updateFromFilters($filters);
        $availableValues = $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel($filters);
        $proVehicleForm = $this->formFactory->create(
            ProVehicleType::class,
            $vehicleDTO,
            [
                'available_values' => $availableValues,
                'action' => $actionRoute
            ]);
        $proVehicleForm->handleRequest($request);

        if ($proVehicleForm->isSubmitted() && $proVehicleForm->isValid()) {
            if ($vehicle) {
                $this->proVehicleEditionService->updateInformations($vehicleDTO, $vehicle);
                $flashMessage = 'flash.success.vehicle_update';
            } else {
                $vehicle = $this->proVehicleEditionService->createInformations($vehicleDTO, $garage);
                $flashMessage = 'flash.success.vehicle_create';
            }

            $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, $flashMessage);
            return $this->redirSave($vehicle, 'front_vehicle_pro_detail');
        }

        return $this->render('front/Vehicle/Add/add.html.twig', [
            'proVehicleForm' => $proVehicleForm->createView(),
            'vehicle' => $vehicle
        ]);
    }

    /**
     * @param Request $request
     * @param ProVehicle $vehicle
     * @return Response
     */
    public function detailAction(Request $request, ProVehicle $vehicle): Response
    {
        $userLike = null;
        if ($this->isUserAuthenticated()) {
            $userLike = $vehicle->getLikeOfUser($this->getUser());
        }
        return $this->render('front/Vehicle/Detail/detail_proVehicle.html.twig', [
            'isEditableByCurrentUser' => $this->proVehicleEditionService->canEdit($this->getUser(), $vehicle),
            'vehicle' => $vehicle,
            'positiveLikes' => $vehicle->getPositiveLikesByUserType(),
            'like' => $userLike
        ]);
    }

    /**
     * @param ProVehicle $proVehicle
     * @return Response
     */
    public function deleteAction(ProVehicle $proVehicle): Response
    {
        if (!$this->proVehicleEditionService->canEdit($this->getUser(), $proVehicle)) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_DANGER,
                'flash.error.remove_vehicle'
            );
            return $this->redirectToRoute('front_vehicle_pro_detail', [
                'id' => $proVehicle->getId()
            ]);
        }

        $this->proVehicleEditionService->deleteVehicle($proVehicle);

        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_INFO,
            'flash.success.remove_vehicle'
        );

        return $this->redirectToRoute('front_garage_view', [
            'id' => $proVehicle->getGarage()->getId()
        ]);
    }

    /**
     * @param ProVehicle $vehicle
     * @param Request $request
     * @return Response
     */
    public function likeProVehicleAction(ProVehicle $vehicle, Request $request): Response
    {
        if (!$this->isUserAuthenticated()) {
            if ($request->headers->has("referer")) {
                $this->session->set(self::LIKE_REDIRECT_TO_SESSION_KEY, $request->headers->get('referer'));
            }
            throw new AccessDeniedException();
        }
        $this->proVehicleEditionService->userLikesVehicle($this->getUser(), $vehicle);

        if ($this->session->has(self::LIKE_REDIRECT_TO_SESSION_KEY) || $request->headers->has("referer")) {
            $referer = $this->session->get(self::LIKE_REDIRECT_TO_SESSION_KEY, $request->headers->get("referer"));
            $this->session->remove(self::LIKE_REDIRECT_TO_SESSION_KEY);
            if (!empty($referer)) {
                // Keep the query param from the request
                $queryParam = '';
                if ($request->query->has(SecurityController::INSCRIPTION_QUERY_PARAM)) {
                    $queryParam = str_contains($referer, '?') ? '&' : '?';
                    $queryParam .= SecurityController::INSCRIPTION_QUERY_PARAM . "=" . $request->query->get(SecurityController::INSCRIPTION_QUERY_PARAM);
                }
                if ($referer === $this->generateUrl('front_vehicle_pro_detail', ['id' => $vehicle->getId()])) {
                    return $this->redirect($referer . $queryParam . '#header-' . $vehicle->getId());
                }
                return $this->redirect($referer . $queryParam . '#' . $vehicle->getId());
            }
        }
        return $this->redirectToRoute("front_vehicle_pro_detail", ['id' => $vehicle->getId()]);
    }
}
