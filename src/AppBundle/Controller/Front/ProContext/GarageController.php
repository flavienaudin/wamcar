<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Elasticsearch\Query\SearchResultProvider;
use AppBundle\Exception\Garage\AlreadyGarageMemberException;
use AppBundle\Exception\Garage\ExistingGarageException;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\Type\GarageType;
use AppBundle\Form\Type\SearchVehicleType;
use AppBundle\Services\Garage\GarageEditionService;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use AppBundle\Session\SessionMessageManager;
use AppBundle\Utils\VehicleInfoAggregator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;

class GarageController extends BaseController
{
    use VehicleTrait;

    const NB_VEHICLES_PER_PAGE = 10;

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var GarageRepository */
    protected $garageRepository;
    /** @var GarageEditionService */
    protected $garageEditionService;
    /** @var SessionMessageManager */
    protected $sessionMessageManager;
    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;
    /** @var SearchResultProvider */
    private $searchResultProvider;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;

    /**
     * GarageController constructor.
     * @param FormFactoryInterface $formFactory
     * @param GarageRepository $garageRepository
     * @param GarageEditionService $garageEditionService
     * @param SessionMessageManager $sessionMessageManager
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     * @param SearchResultProvider $searchResultProvider
     * @param ProVehicleEditionService $proVehicleEditionService
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        GarageRepository $garageRepository,
        GarageEditionService $garageEditionService,
        SessionMessageManager $sessionMessageManager,
        VehicleInfoAggregator $vehicleInfoAggregator,
        SearchResultProvider $searchResultProvider,
        ProVehicleEditionService $proVehicleEditionService
    )
    {
        $this->formFactory = $formFactory;
        $this->garageRepository = $garageRepository;
        $this->garageEditionService = $garageEditionService;
        $this->sessionMessageManager = $sessionMessageManager;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->searchResultProvider = $searchResultProvider;
        $this->proVehicleEditionService = $proVehicleEditionService;
    }

    /**
     * security.yml - access_control : ROLE_ADMIN only
     * @return Response
     */
    public function indexAction(): Response
    {
        $lastGarages = $this->garageRepository->getLatest();

        return $this->render('front/adminContext/garage/garage_list.html.twig', [
            'garages' => $lastGarages
        ]);
    }

    /**
     * @param Request $request
     * @param Garage $garage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, Garage $garage)
    {
        $searchForm = null;
        if (count($garage->getProVehicles()) > self::NB_VEHICLES_PER_PAGE) {
            $searchVehicleDTO = new SearchVehicleDTO();
            $searchForm = $this->formFactory->create(SearchVehicleType::class, $searchVehicleDTO, [
                'action' => $this->generateRoute('front_garage_view', ['id' => $garage->getId()]),
                'available_values' => [],
                'small_version' => true
            ]);
            $searchForm->handleRequest($request);
            $page = $request->query->get('page', 1);
            $searchResult = $this->searchResultProvider->getQueryGarageVehiclesResult($garage, $searchForm->get("text")->getData(), $page, self::NB_VEHICLES_PER_PAGE);
            $vehicles = $this->proVehicleEditionService->getVehiclesBySearchResult($searchResult);
            $lastPage = $searchResult->numberOfPages();
        } else {
            $vehicles = [
                'totalHits' => count($garage->getProVehicles()),
                'hits' => $this->proVehicleEditionService->getVehiclesByGarage($garage)
            ];
        }

        return $this->render('front/Garages/Detail/detail.html.twig', [
            'isEditableByCurrentUser' => $this->garageEditionService->canEdit($this->getUser(), $garage),
            'garage' => $garage,
            'vehicles' => $vehicles,
            'page' => $page ?? null,
            'lastPage' => $lastPage ?? null,
            'garagePlaceDetail' => $this->garageEditionService->getGooglePlaceDetails($garage),
            'searchForm' => $searchForm ? $searchForm->createView() : null
        ]);
    }

    /**
     * @param Request $request
     * @Security("has_role('ROLE_PRO')")
     * @param null|Garage $garage
     * @return RedirectResponse|Response
     */
    public function saveAction(Request $request, ?Garage $garage)
    {
        if (null !== $garage && !$this->authorizationChecker->isGranted('edit', $garage)) {
            throw new AccessDeniedHttpException('Only member can access edit this garage');
        }

        $garageDTO = new GarageDTO($garage);
        $garageForm = $this->formFactory->create(GarageType::class, $garageDTO);
        $garageForm->handleRequest($request);

        if ($garageForm->isSubmitted() && $garageForm->isValid()) {
            try {
                $garage = $this->garageEditionService->editInformations($garageDTO, $garage, $this->getUser());

                $successMessage = (null === $garage ? 'flash.success.garage.create' : 'flash.success.garage.edit');
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_INFO,
                    $successMessage
                );
            } catch (ExistingGarageException $e) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_WARNING,
                    'flash.warning.garage.same_as_existing'
                );
                return $this->redirectToRoute('front_garage_view', [
                    'id' => $e->getGarage()->getId(),
                    '_fragment' => 'sellers']);
            } catch (AlreadyGarageMemberException $e) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_WARNING,
                    'flash.warning.garage.already_member'
                );
                return $this->redirectToRoute('front_view_current_user_info');
            }
            return $this->redirSave([], 'front_garage_view', ['id' => $garage->getId()]);
        }

        return $this->render('front/Garages/Edit/edit.html.twig', [
            'garage' => $garage,
            'isNew' => $garageDTO->isNew,
            'garageForm' => $garageForm->createView(),
        ]);
    }

    /**
     * @param Garage $garage
     * @Security("has_role('ROLE_ADMIN')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Garage $garage): RedirectResponse
    {
        $this->garageEditionService->remove($garage);

        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_INFO,
            'flash.success.remove_garage'
        );

        return $this->redirectToRoute('front_garage_list');
    }

    /**
     * @param Garage $garage
     * @Security("has_role('ROLE_PRO')")
     * @return Response
     */
    public function requestToJoinAction(Garage $garage): Response
    {
        /** @var ProApplicationUser $user */
        $user = $this->getUser();
        if ($user->isMemberOfGarage($garage)) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_WARNING,
                'flash.warning.garage.already_member'
            );
            return $this->redirectToRoute('front_view_current_user_info');
        } else {
            $this->garageEditionService->addMember($garage, $user, false);
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.garage.request_sent_to_administrator'
            );
            return $this->redirectToRoute('front_view_current_user_info');
        }
    }

    /**
     * @param Garage $garage
     * @Security("has_role('ROLE_PRO')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function assignAction(Garage $garage): RedirectResponse
    {
        throw new AccessDeniedHttpException('Action not used');

        $user = $this->getUser();

        $this->garageEditionService->addMember($garage, $user, false);

        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_INFO,
            'flash.success.add_member_garage'
        );

        return $this->redirectToRoute('front_garage_list');
    }

    /**
     * @ParamConverter("garage", options={"id" = "garage_id"})
     * @ParamConverter("user", options={"id" = "user_id"})
     * @param Garage $garage
     * @param ProApplicationUser $user
     * @Security("has_role('ROLE_PRO')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unassignAction(Garage $garage, ProApplicationUser $user): RedirectResponse
    {
        throw new AccessDeniedHttpException('Action not used');

        $this->garageEditionService->removeMember($garage, $user);

        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_INFO,
            'flash.success.add_member_garage'
        );

        return $this->redirectToRoute('front_garage_list');
    }
}
