<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Elasticsearch\Elastica\ElasticUtils;
use AppBundle\Elasticsearch\Elastica\ProVehicleEntityIndexer;
use AppBundle\Exception\Garage\AlreadyGarageMemberException;
use AppBundle\Exception\Garage\ExistingGarageException;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\DTO\GaragePictureDTO;
use AppBundle\Form\DTO\GaragePresentationDTO;
use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\Type\GaragePictureType;
use AppBundle\Form\Type\GaragePresentationType;
use AppBundle\Form\Type\GarageProInvitationType;
use AppBundle\Form\Type\GarageType;
use AppBundle\Form\Type\SearchVehicleType;
use AppBundle\Security\Voter\GarageVoter;
use AppBundle\Services\Garage\GarageEditionService;
use AppBundle\Services\User\CanBeGarageMember;
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
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;
use Wamcar\Garage\GarageRepository;
use Wamcar\User\ProUser;

class GarageController extends BaseController
{
    use VehicleTrait;

    const NB_VEHICLES_PER_PAGE = 12;

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
     * security.yml - access_control : ROLE_PRO_ADMIN only
     * @return Response
     */
    public function listAction(): Response
    {
        $garages = $this->garageRepository->findIgnoreSoftDeletedBy([], ['id' => 'desc']);

        return $this->render('front/adminContext/garage/garage_list.html.twig', [
            'garages' => $garages
        ]);
    }

    /**
     * @Entity("garage", expr="repository.findIgnoreSoftDeletedOneBy({'slug':slug})")
     * @param Request $request
     * @param string $slug
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, string $slug)
    {
        /** @var Garage $garage */
        $garage = $this->garageRepository->findIgnoreSoftDeletedOneBy(['slug' => $slug]);
        if ($garage === null || $garage->getDeletedAt() != null) {
            $response = $this->render('front/Exception/error410.html.twig', [
                'titleKey' => 'error_page.garage.deleted.title',
                'messageKey' => 'error_page.garage.deleted.body',
                'redirectionUrl' => $this->generateUrl('front_view_current_user_info')
            ]);
            $response->setStatusCode(Response::HTTP_GONE);
            return $response;
        }

        $currentUser = $this->getUser();
        /** @var GarageProUser $currentUserGarageMemberShip */
        $currentUserGarageMemberShip = $currentUser instanceof CanBeGarageMember ? $currentUser->getMembershipByGarage($garage) : null;

        if ($garage->getPublishableMembers()->count() == 0 && $currentUserGarageMemberShip == null) {
            $response = $this->render('front/Exception/error_message.html.twig', [
                'titleKey' => 'error_page.garage.unpublished.title',
                'messageKey' => 'error_page.garage.unpublished.body',
                'redirectionUrl' => $this->generateUrl('front_view_current_user_info')
            ]);
            $response->setStatusCode(Response::HTTP_OK);
            return $response;
        }

        /** Masquage des annonces pro
        // Véhicucle search form
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
            if ($searchResultSet != null) {
                $vehicles = $this->proVehicleEditionService->getVehiclesBySearchResult($searchResultSet);
                $lastPage = ElasticUtils::numberOfPages($searchResultSet);
            } else {
                $vehicles = ['totalHits' => 0, 'hits' => []];
                $lastPage = 1;
            }
        } else {
            $vehicles = [
                'totalHits' => count($garage->getProVehicles()),
                'hits' => $garage->getProVehicles()
            ];
        }
        */

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


        $garageBannerForm = null;
        $garageLogoForm = null;
        $garagePresentationForm = null;
        if ($this->isGranted(GarageVoter::EDIT, $garage)) {
            // Garage Banner Form
            $garageBannerDTO = new GaragePictureDTO($garage->getBannerFile());
            $garageBannerForm = $this->formFactory->createNamed('garage_banner', GaragePictureType::class, $garageBannerDTO);
            $garageBannerForm->handleRequest($request);
            if ($garageBannerForm->isSubmitted() && $garageBannerForm->isValid()) {
                $this->garageEditionService->editBanner($garageBannerDTO, $garage);
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.garage.edit');
                return $this->redirectToRoute('front_garage_view', ['slug' => $garage->getSlug()]);
            }

            // Garage Logo Form
            $garageLogoDTO = new GaragePictureDTO($garage->getLogoFile());
            $garageLogoForm = $this->formFactory->createNamed('garage_logo', GaragePictureType::class, $garageLogoDTO);
            $garageLogoForm->handleRequest($request);
            if ($garageLogoForm->isSubmitted() && $garageLogoForm->isValid()) {
                $this->garageEditionService->editLogo($garageLogoDTO, $garage);
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.garage.edit');
                return $this->redirectToRoute('front_garage_view', ['slug' => $garage->getSlug()]);
            }

            // Presentation Form
            $garagePresentationDTO = new GaragePresentationDTO($garage);
            $garagePresentationForm = $this->formFactory->create(GaragePresentationType::class, $garagePresentationDTO);
            $garagePresentationForm->handleRequest($request);
            if ($garagePresentationForm->isSubmitted() && $garagePresentationForm->isValid()) {
                if ($garagePresentationForm->isValid()) {
                    $this->garageEditionService->editPresentationInformations($garagePresentationDTO, $garage);
                    $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.garage.edit');
                    return $this->redirectToRoute('front_garage_view', ['slug' => $garage->getSlug()]);
                } else {
                    $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.garage.edit');
                }
            }
        }
        return $this->render('front/Garages/Detail/detail_peexeo.html.twig', [
            'isEditableByCurrentUser' => $this->isGranted(GarageVoter::EDIT, $garage),
            'isAdministrableByCurrentUser' => $this->isGranted(GarageVoter::ADMINISTRATE, $garage),
            'currentUserGarageMemberShip' => $currentUserGarageMemberShip,
            'currentUserIsMemberOfGarage' => $currentUserGarageMemberShip != null && $currentUserGarageMemberShip->getRequestedAt() == null,
            'garage' => $garage,
            //'vehicles' => $vehicles,
            //'page' => $page ?? null,
            //'lastPage' => $lastPage ?? null,
            //'searchForm' => $searchForm ? $searchForm->createView() : null,
            'garagePlaceDetail' => $this->garageEditionService->getGooglePlaceDetails($garage),
            'inviteSellerForm' => $inviteSellerForm ? $inviteSellerForm->createView() : null,
            'garageBannerForm' => $garageBannerForm ? $garageBannerForm->createView() : null,
            'garageLogoForm' => $garageLogoForm ? $garageLogoForm->createView() : null,
            'garagePresentationForm' => $garagePresentationForm ? $garagePresentationForm->createView() : null
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
                        ])]));
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
     * @param Request $request
     * @param Garage $garage
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Request $request, Garage $garage): RedirectResponse
    {
        if (!$this->isGranted(GarageVoter::ADMINISTRATE, $garage)) {
            $this->session->getFlashBag()->add(BaseController::FLASH_LEVEL_DANGER, 'flash.error.garage.unauthorized_to_administrate');

            if ($request->headers->has(self::REQUEST_HEADER_REFERER)) {
                return $this->redirect($request->headers->get(self::REQUEST_HEADER_REFERER));
            } elseif ($this->getUser() instanceof ProUser) {
                return $this->redirectToRoute('front_view_current_user_info');
            } else {
                return $this->redirectToRoute('front_view_current_user_info');
            }
        }

        $isAlreadySoftDeleted = $garage->getDeletedAt() != null;
        try {
            $this->garageEditionService->remove($garage);
            if ($isAlreadySoftDeleted) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_INFO,
                    'flash.success.garage.deleted.hard'
                );
            } else {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_INFO,
                    'flash.success.garage.deleted.soft'
                );
            }
        } catch (\InvalidArgumentException $exception) {
            $this->session->getFlashBag()->add(
                BaseController::FLASH_LEVEL_WARNING,
                $exception->getMessage()
            );
            return $this->redirectToRoute('front_garage_view', ['slug' => $garage->getSlug()]);
        }
        if ($request->headers->has(self::REQUEST_HEADER_REFERER) &&
            $this->generateUrl('front_garage_view', ['slug' => $garage->getSlug()]) != $request->headers->get(self::REQUEST_HEADER_REFERER)) {
            return $this->redirect($request->headers->get(self::REQUEST_HEADER_REFERER));
        } elseif ($this->getUser() instanceof ProUser) {
            return $this->redirectToRoute('front_view_current_user_info');
        }
        return $this->redirectToRoute('front_view_current_user_info');
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
            } else {
                // New pending request
                $this->garageEditionService->addMember($garage, $proApplicationUser, false, false);
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_INFO,
                    'flash.success.garage.request_sent_to_administrator'
                );
            }
            return $this->redirectToRoute('front_garage_view', [
                'slug' => $garage->getSlug(),
                '_fragment' => 'sellers'
            ]);
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
        /** @var GarageProUser $garageMemberShip */
        $garageMemberShip = $proApplicationUser->getMembershipByGarage($garage);
        if ($garageMemberShip == null) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.error.garage.not_member');
        } else {
            if (!$garageMemberShip->getProUser()->is($this->getUser()) && !$this->authorizationChecker->isGranted(GarageVoter::ADMINISTRATE, $garage)) {
                throw new AccessDeniedException();
            }
            $result = $this->garageEditionService->removeMemberShip($garageMemberShip, $this->getUser());
            if ($result['memberRemovedErrorMessage'] != null) {
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, $result['memberRemovedErrorMessage']);
            } else {
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, $result['memberRemovedSuccessMessage']);
            }
        }
        return $this->redirectToRoute('front_garage_view', [
            'slug' => $garage->getSlug(),
            '_fragment' => 'sellers'
        ]);
    }

    /**
     * @ParamConverter("garage", options={"id" = "garage_id"})
     * @ParamConverter("proApplicationUser", options={"id" = "user_id"})
     * @param Request $request
     * @param Garage $garage
     * @param ProApplicationUser $proApplicationUser
     * @param bool $replace
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toogleMemberRoleAction(Request $request, Garage $garage, ProApplicationUser $proApplicationUser, bool $replace): RedirectResponse
    {
        /** @var GarageProUser $garageMemberShip */
        $garageMemberShip = $proApplicationUser->getMembershipByGarage($garage);
        if ($garageMemberShip == null) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.error.garage.not_member');
        } else {
            if (!$this->authorizationChecker->isGranted(GarageVoter::ADMINISTRATE, $garage)) {
                throw new AccessDeniedException();
            }
            try {
                $this->garageEditionService->toogleRole($garageMemberShip);
                if ($replace) {
                    $currentUserGarageMemberShip = $this->getUser()->getMembershipByGarage($garage);
                    $this->garageEditionService->toogleRole($currentUserGarageMemberShip);
                    $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.garage.designate_as_administrator');
                } else {
                    $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.garage.toogle_role');
                }
            } catch (\InvalidArgumentException $e) {
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, $e->getMessage());
            }
        }
        if ($referer = $this->getReferer($request)) {
            return $this->redirect($referer);
        } else {
            return $this->redirectToRoute('admin_garage_list', ['_fragment' => 'garage-' . $garage->getId()]);
        }
    }
}
