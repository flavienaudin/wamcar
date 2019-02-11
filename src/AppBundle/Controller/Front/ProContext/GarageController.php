<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Annotation\IgnoreSoftDeleted;
use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Elasticsearch\Elastica\ElasticUtils;
use AppBundle\Elasticsearch\Elastica\ProVehicleEntityIndexer;
use AppBundle\Exception\Garage\AlreadyGarageMemberException;
use AppBundle\Exception\Garage\ExistingGarageException;
use AppBundle\Exception\Vehicle\NewSellerToAssignNotFoundException;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\Type\GarageProInvitationType;
use AppBundle\Form\Type\GarageType;
use AppBundle\Form\Type\SearchVehicleType;
use AppBundle\Security\Voter\GarageVoter;
use AppBundle\Services\Garage\GarageEditionService;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use AppBundle\Session\SessionMessageManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Garage\Enum\GarageRole;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;
use Wamcar\Garage\GarageRepository;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\ProVehicle;

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
    /** @var ProVehicleEntityIndexer */
    private $proVehicleEntityIndexer;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;
    /** @var TranslatorInterface $translator */
    private $translator;

    /**
     * GarageController constructor.
     * @param FormFactoryInterface $formFactory
     * @param GarageRepository $garageRepository
     * @param GarageEditionService $garageEditionService
     * @param SessionMessageManager $sessionMessageManager
     * @param ProVehicleEntityIndexer $proVehicleEntityIndexer
     * @param ProVehicleEditionService $proVehicleEditionService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        GarageRepository $garageRepository,
        GarageEditionService $garageEditionService,
        SessionMessageManager $sessionMessageManager,
        ProVehicleEntityIndexer $proVehicleEntityIndexer,
        ProVehicleEditionService $proVehicleEditionService,
        TranslatorInterface $translator
    )
    {
        $this->formFactory = $formFactory;
        $this->garageRepository = $garageRepository;
        $this->garageEditionService = $garageEditionService;
        $this->sessionMessageManager = $sessionMessageManager;
        $this->proVehicleEntityIndexer = $proVehicleEntityIndexer;
        $this->proVehicleEditionService = $proVehicleEditionService;
        $this->translator = $translator;
    }

    /**
     * security.yml - access_control : ROLE_ADMIN only
     * @return Response
     */
    public function listAction(): Response
    {
        $garages = $this->garageRepository->findIgnoreSoftDeletedBy([],['id' => 'desc']);

        return $this->render('front/adminContext/garage/garage_list.html.twig', [
            'garages' => $garages
        ]);
    }

    /**
     * @Entity("garage", expr="repository.findIgnoreSoftDeletedOneBy({'slug':slug})")
     * @param Request $request
     * @param Garage $garage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, Garage $garage)
    {
        if($garage->getDeletedAt() != null){
            $response = $this->render('front/Exception/error410.html.twig', [
                'titleKey' => 'error_page.garage.deleted.title',
                'messageKey' => 'error_page.garage.deleted.body',
                'redirectionUrl' => $this->generateUrl('front_directory_by_city_view', [
                    'city' => $garage->getCity()->getPostalCode() . "-". urlencode($garage->getCity()->getName())
                ])
            ]);
            $response->setStatusCode(Response::HTTP_GONE);
            return $response;
        }
        $searchForm = null;
        if (count($garage->getProVehicles()) > self::NB_VEHICLES_PER_PAGE) {
            $searchVehicleDTO = new SearchVehicleDTO();
            $searchForm = $this->formFactory->create(SearchVehicleType::class, $searchVehicleDTO, [
                'action' => $this->generateRoute('front_garage_view', ['slug' => $garage->getSlug()]),
                'available_values' => [],
                'small_version' => true
            ]);
            $searchForm->handleRequest($request);
            $page = $request->query->get('page', 1);
            $searchResultSet = $this->proVehicleEntityIndexer->getQueryGarageVehiclesResult($garage->getId(), $searchForm->get("text")->getData(), $page, self::NB_VEHICLES_PER_PAGE);
            if($searchResultSet != null) {
                $vehicles = $this->proVehicleEditionService->getVehiclesBySearchResult($searchResultSet);
                $lastPage = ElasticUtils::numberOfPages($searchResultSet);
            }else{
                $vehicles = [];
                $lastPage = 1;
            }
        } else {
            $vehicles = [
                'totalHits' => count($garage->getProVehicles()),
                'hits' => $this->proVehicleEditionService->getVehiclesByGarage($garage)
            ];
        }

        $inviteSellerForm = null;
        if ($this->isGranted(GarageVoter::ADMINISTRATE, $garage)) {
            $inviteSellerForm = $this->formFactory->create(GarageProInvitationType::class);
            $inviteSellerForm->handleRequest($request);
            if ($inviteSellerForm->isSubmitted() && $inviteSellerForm->isValid()) {
                $results = $this->garageEditionService->inviteMember($garage, $inviteSellerForm->get('emails')->getData());

                // User automatically attached to the garage
                if (count($results[GarageEditionService::INVITATION_EMAIL_ATTACHED]) > 0) {
                    $prousersAttachedList = '';
                    /**
                     * @var string $email
                     * @var ProUser $proUser
                     */
                    foreach ($results[GarageEditionService::INVITATION_EMAIL_ATTACHED] as $email => $proUser) {
                        $prousersAttachedList .=
                            (strlen($prousersAttachedList) > 0 ? ', ' : '') . $proUser->getFullName() . ' (' . $email . ')';
                    }

                    $this->session->getFlashBag()->add(
                        self::FLASH_LEVEL_INFO,
                        $this->translator->transChoice('flash.success.garage.invitations.attached',
                            count($results[GarageEditionService::INVITATION_EMAIL_ATTACHED]),
                            [
                                '%prousers_attached_list%' => $prousersAttachedList,
                                '%garage_name%' => $garage->getName()
                            ]
                        ));
                }
                // User invited to register
                if (count($results[GarageEditionService::INVITATION_EMAIL_INVITED]) > 0) {
                    $emailsPersonalList = join(', ', $results[GarageEditionService::INVITATION_EMAIL_INVITED]);

                    $this->session->getFlashBag()->add(
                        self::FLASH_LEVEL_INFO,
                        $this->translator->transChoice('flash.success.garage.invitations.invited',
                            count($results[GarageEditionService::INVITATION_EMAIL_INVITED]),
                            [
                                '%prousers_invited_list%' => $emailsPersonalList
                            ]
                        ));
                }

                // Email of personal users
                if (count($results[GarageEditionService::INVITATION_EMAIL_PERSONAL]) > 0) {
                    $emailsPersonalList = join(', ', array_keys($results[GarageEditionService::INVITATION_EMAIL_PERSONAL]));
                    $this->session->getFlashBag()->add(
                        self::FLASH_LEVEL_WARNING,
                        $this->translator->transChoice('flash.warning.garage.invitations.personal',
                            count($results[GarageEditionService::INVITATION_EMAIL_PERSONAL]),
                            [
                                '%prousers_personal_list%' => $emailsPersonalList,
                                '%garage_name%' => $garage->getName()
                            ]
                        ));
                }

                return $this->redirectToRoute('front_garage_view', [
                    'slug' => $garage->getSlug(),
                    '_fragment' => 'sellers']);
            }
        }

        return $this->render('front/Garages/Detail/detail.html.twig', [
            'isEditableByCurrentUser' => $this->garageEditionService->canEdit($this->getUser(), $garage),
            'garage' => $garage,
            'vehicles' => $vehicles,
            'page' => $page ?? null,
            'lastPage' => $lastPage ?? null,
            'garagePlaceDetail' => $this->garageEditionService->getGooglePlaceDetails($garage),
            'searchForm' => $searchForm ? $searchForm->createView() : null,
            'inviteSellerForm' => $inviteSellerForm ? $inviteSellerForm->createView() : null
        ]);
    }

    /**
     * @Entity("garage", expr="repository.findIgnoreSoftDeleted(id)")
     * @param Garage $garage
     * @return RedirectResponse
     */
    public function legacyViewAction(Garage $garage): RedirectResponse
    {
        return $this->redirectToRoute('front_garage_view', ['slug' => $garage->getSlug()], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @param Request $request
     * @Security("has_role('ROLE_PRO')")
     * @param null|Garage $garage
     * @return RedirectResponse|Response
     */
    public function saveAction(Request $request, ?Garage $garage)
    {
        if (null !== $garage && !$this->authorizationChecker->isGranted(GarageVoter::EDIT, $garage)) {
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
                    $this->translator->trans('flash.warning.garage.same_as_existing', [
                            '%requestUrl%' => $this->generateRoute('front_garage_request_to_join', [
                                'garage_id' => $e->getGarage()->getId()
                            ])]
                    ));
                return $this->redirectToRoute('front_garage_view', [
                    'slug' => $e->getGarage()->getSlug(),
                    '_fragment' => 'sellers']);
            } catch (AlreadyGarageMemberException $e) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_WARNING,
                    'flash.warning.garage.already_member'
                );
                return $this->redirectToRoute('front_view_current_user_info');
            }
            return $this->redirSave([], 'front_garage_view', ['slug' => $garage->getSlug()]);
        }

        return $this->render('front/Garages/Edit/edit.html.twig', [
            'garage' => $garage,
            'isNew' => $garageDTO->isNew,
            'garageForm' => $garageForm->createView(),
        ]);
    }

    /**
     * @Entity("garage", expr="repository.findIgnoreSoftDeleted(id)")
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

        return $this->redirectToRoute('admin_garage_list');
    }

    /**
     * @ParamConverter("garage", options={"id" = "garage_id"})
     * @ParamConverter("proApplicationUser", options={"id" = "user_id"})
     * @param Garage $garage
     * @param ProApplicationUser|null $proApplicationUser
     * @Security("has_role('ROLE_PRO')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function assignAction(Garage $garage, ?ProApplicationUser $proApplicationUser = null): RedirectResponse
    {
        if ($proApplicationUser == null) {
            $proApplicationUser = $this->getUser();
        }
        $member = $proApplicationUser->getMembershipByGarage($garage);
        if ($member != null) {
            if ($member->getRequestedAt() == null) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_WARNING,
                    'flash.warning.garage.already_member'
                );
                return $this->redirectToRoute('front_view_current_user_info');
            } else {
                // pending request
                if ($this->isGranted(GarageVoter::ADMINISTRATE, $garage)) {
                    $this->garageEditionService->acceptPendingRequest($member);

                    $this->session->getFlashBag()->add(
                        self::FLASH_LEVEL_INFO,
                        'flash.success.garage.assign_member'
                    );
                    return $this->redirectToRoute('front_garage_view', [
                        'slug' => $garage->getSlug(),
                        '_fragment' => 'sellers'
                    ]);
                } else {
                    $this->session->getFlashBag()->add(
                        self::FLASH_LEVEL_DANGER,
                        'flash.error.garage.unauthorized_to_administrate'
                    );
                    return $this->redirectToRoute('front_garage_view', [
                        'slug' => $garage->getSlug(),
                        '_fragment' => 'sellers'
                    ]);
                }
            }
        } else {

            if ($this->isGranted(GarageVoter::ADMINISTRATE, $garage)) {
                // Assignation by an adminsitrator
                $this->garageEditionService->addMember($garage, $proApplicationUser, false, true);
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_INFO,
                    'flash.success.garage.assign_member'
                );
                return $this->redirectToRoute('front_garage_view', [
                    'slug' => $garage->getSlug(),
                    '_fragment' => 'sellers'
                ]);
            } else {
                // New pending request
                $this->garageEditionService->addMember($garage, $proApplicationUser, false, false);
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_INFO,
                    'flash.success.garage.request_sent_to_administrator'
                );
            }
            return $this->redirectToRoute('front_view_current_user_info');
        }
    }

    /**
     * @ParamConverter("garage", options={"id" = "garage_id"})
     * @ParamConverter("proApplicationUser", options={"id" = "user_id"})
     * @param Garage $garage
     * @param ProApplicationUser $proApplicationUser
     * @Security("has_role('ROLE_PRO')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unassignAction(Garage $garage, ProApplicationUser $proApplicationUser): RedirectResponse
    {
        /** @var GarageProUser $member */
        $member = $proApplicationUser->getMembershipByGarage($garage);
        if ($member == null) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.error.garage.not_member'
            );
            return $this->redirectToRoute('front_garage_view', [
                'slug' => $garage->getSlug(),
                '_fragment' => 'sellers'
            ]);
        }
        if (!$member->getProUser()->is($this->getUser()) && !$this->authorizationChecker->isGranted(GarageVoter::ADMINISTRATE, $garage)) {
            throw new AccessDeniedException();
        }

        if ($member->getRequestedAt() == null) {
            // Unassign garage member
            if (GarageRole::GARAGE_ADMINISTRATOR()->equals($member->getRole())) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_WARNING,
                    'flash.error.garage.remove_administrator'
                );
            } else {
                $userVehicles = $proApplicationUser->getVehiclesOfGarage($garage);
                if (count($userVehicles) > 0) {
                    // Vehicle distribution to other garage members

                    $nbVehiclesNotAssigned = 0;
                    /** @var ProVehicle $vehicle */
                    foreach ($userVehicles as $vehicle) {
                        try {
                            $this->proVehicleEditionService->assignSeller($vehicle);

                        } catch (NewSellerToAssignNotFoundException $e) {
                            $nbVehiclesNotAssigned++;
                            $this->session->getFlashBag()->add(
                                self::FLASH_LEVEL_DANGER,
                                'flash.error.vehicle.seller_to_reassign_not_found'
                            );
                        } catch (\InvalidArgumentException $e) {
                            $nbVehiclesNotAssigned++;
                            $this->session->getFlashBag()->add(
                                self::FLASH_LEVEL_DANGER,
                                $e->getMessage()
                            );
                        }
                    }

                    if ($nbVehiclesNotAssigned > 0) {
                        $this->session->getFlashBag()->add(
                            self::FLASH_LEVEL_WARNING,
                            'flash.error.garage.unable_to_detach_seller.due_to_attached_vehicle'
                        );
                    } else {
                        $this->garageEditionService->removeMember($garage, $proApplicationUser);
                        $this->session->getFlashBag()->add(
                            self::FLASH_LEVEL_INFO,
                            'flash.success.garage.remove_member_with_reassignation'
                        );
                    }
                } else {
                    $this->garageEditionService->removeMember($garage, $proApplicationUser);
                    $this->session->getFlashBag()->add(
                        self::FLASH_LEVEL_INFO,
                        'flash.success.garage.remove_member'
                    );
                }
            }
        } else {
            // Pending request
            if ($proApplicationUser->is($this->getUser())) {
                // Cancelled by the proUser
                $this->garageEditionService->removeMember($garage, $proApplicationUser, false);
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_INFO,
                    'flash.success.garage.cancel_pending_request_by_user'
                );
            } else {
                // Declined by an administrator
                $this->garageEditionService->removeMember($garage, $proApplicationUser, true);
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_INFO,
                    'flash.success.garage.cancel_pending_request_by_administrator'
                );
            }
        }

        return $this->redirectToRoute('front_garage_view', [
            'slug' => $garage->getSlug(),
            '_fragment' => 'sellers'
        ]);
    }
}
