<?php

namespace AppBundle\Controller\Front\PersonalContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Controller\Front\ProContext\SearchController;
use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository;
use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Elasticsearch\Elastica\ElasticUtils;
use AppBundle\Elasticsearch\Elastica\ProVehicleEntityIndexer;
use AppBundle\Elasticsearch\Elastica\VehicleInfoEntityIndexer;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\DTO\ProjectDTO;
use AppBundle\Form\DTO\ProUserInformationDTO;
use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\DTO\UserInformationDTO;
use AppBundle\Form\DTO\UserPreferencesDTO;
use AppBundle\Form\Type\GarageType;
use AppBundle\Form\Type\PersonalUserInformationType;
use AppBundle\Form\Type\ProjectType;
use AppBundle\Form\Type\ProUserInformationType;
use AppBundle\Form\Type\SearchVehicleType;
use AppBundle\Form\Type\UserAvatarType;
use AppBundle\Form\Type\UserPreferencesType;
use AppBundle\Security\Voter\UserVoter;
use AppBundle\Services\Affinity\AffinityAnswerCalculationService;
use AppBundle\Services\Garage\GarageEditionService;
use AppBundle\Services\User\UserEditionService;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Garage\GarageProUser;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\PersonalProjectUpdated;
use Wamcar\User\Event\PersonalUserUpdated;
use Wamcar\User\Event\ProUserUpdated;
use Wamcar\User\PersonalUser;
use Wamcar\User\Project;
use Wamcar\User\ProUser;
use Wamcar\User\UserRepository;

class UserController extends BaseController
{
    const NB_VEHICLES_PER_PAGE = 10;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var UserRepository */
    protected $userRepository;

    /** @var DoctrinePersonalUserRepository */
    protected $personalUserRepository;

    /** @var DoctrineProUserRepository */
    protected $proUserRepository;

    /** @var UserEditionService */
    protected $userEditionService;

    /** @var GarageEditionService */
    protected $garageEditionService;

    /** @var VehicleInfoEntityIndexer */
    private $vehicleInfoIndexer;

    /** @var ProVehicleEntityIndexer */
    private $proVehicleEntityIndexer;

    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;

    /** @var MessageBus */
    protected $eventBus;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var AffinityAnswerCalculationService $affinityAnswerCalculationService */
    protected $affinityAnswerCalculationService;

    /**
     * SecurityController constructor.
     * @param FormFactoryInterface $formFactory
     * @param UserRepository $userRepository
     * @param DoctrinePersonalUserRepository $personalUserRepository
     * @param DoctrineProUserRepository $proUserRepository
     * @param UserEditionService $userEditionService
     * @param GarageEditionService $garageEditionService
     * @param VehicleInfoEntityIndexer $vehicleInfoIndexer
     * @param ProVehicleEntityIndexer $proVehicleEntityIndexer
     * @param ProVehicleEditionService $proVehicleEditionService
     * @param MessageBus $eventBus
     * @param TranslatorInterface $translator
     * @param AffinityAnswerCalculationService $affinityAnswerCalculationService
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UserRepository $userRepository,
        DoctrinePersonalUserRepository $personalUserRepository,
        DoctrineProUserRepository $proUserRepository,
        UserEditionService $userEditionService,
        GarageEditionService $garageEditionService,
        VehicleInfoEntityIndexer $vehicleInfoIndexer,
        ProVehicleEntityIndexer $proVehicleEntityIndexer,
        ProVehicleEditionService $proVehicleEditionService,
        MessageBus $eventBus,
        TranslatorInterface $translator,
        AffinityAnswerCalculationService $affinityAnswerCalculationService
    )
    {
        $this->formFactory = $formFactory;
        $this->userRepository = $userRepository;
        $this->personalUserRepository = $personalUserRepository;
        $this->proUserRepository = $proUserRepository;
        $this->userEditionService = $userEditionService;
        $this->garageEditionService = $garageEditionService;
        $this->vehicleInfoIndexer = $vehicleInfoIndexer;
        $this->proVehicleEntityIndexer = $proVehicleEntityIndexer;
        $this->proVehicleEditionService = $proVehicleEditionService;
        $this->eventBus = $eventBus;
        $this->translator = $translator;
        $this->affinityAnswerCalculationService = $affinityAnswerCalculationService;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function editInformationsAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);

        /** @var ApplicationUser $user */
        $user = $this->getUser();

        $userProfileTemplate = [
            ProApplicationUser::TYPE => 'front/Seller/edit.html.twig',
            PersonalApplicationUser::TYPE => 'front/User/edit.html.twig',
        ];
        $userDTOs = [
            ProApplicationUser::TYPE => ProUserInformationDTO::class,
            PersonalApplicationUser::TYPE => UserInformationDTO::class
        ];
        /** @var UserInformationDTO $userInformationDTO */
        $userInformationDTO = new $userDTOs[$user->getType()]($user);

        $editForm = $this->createEditForm($user, $userInformationDTO);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->userEditionService->editInformations($user, $userInformationDTO);
            if ($user->getType() === PersonalUser::TYPE) {
                $this->eventBus->handle(new PersonalUserUpdated($user));
                if ($user->getProject() != null) {
                    $this->eventBus->handle(new PersonalProjectUpdated($user->getProject()));
                }
            } else {
                $this->eventBus->handle(new ProUserUpdated($user));
            }

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.user.edit'
            );

            return $this->redirectToRoute('front_view_current_user_info');
        }

        return $this->render($userProfileTemplate[$user->getType()], [
            'editUserForm' => $editForm->createView(),
            'user' => $user
        ]);
    }


    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function editProjectAction(Request $request): Response
    {
        /** @var ApplicationUser $user */
        $user = $this->getUser();

        if ($user instanceof PersonalUser) {
            if ($user->getProject() === null) {
                $user->setProject(new Project($user));
            }
            $projectDTO = ProjectDTO::buildFromProject($user->getProject());
            $projectForm = $this->createProjectForm($projectDTO);
            $projectForm->handleRequest($request);

            if ($projectForm->isSubmitted() && $projectForm->isValid()) {
                $this->userEditionService->projectInformations($user, $projectDTO);
                if ($user->getCity() === null || $user->getCity() != $projectDTO->getCity()) {
                    $this->userEditionService->updateUserCity($user, $projectDTO->getCity());
                }

                $this->eventBus->handle(new PersonalProjectUpdated($user->getProject()));

                if ($this->session->has(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY)) {
                    $this->session->getFlashBag()->add(
                        self::FLASH_LEVEL_INFO,
                        'flash.success.registration.personal.assitant.process_end'
                    );
                } else {
                    $this->session->getFlashBag()->add(
                        self::FLASH_LEVEL_INFO,
                        'flash.success.user.edit'
                    );
                }

                return $this->redirectToRoute('front_view_current_user_info');
            }

            return $this->render('front/User/project_edit.html.twig', [
                'projectForm' => $projectForm->createView(),
                'user' => $user
            ]);
        } else {
            // TODO : Throw Exception
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_DANGER,
                'flash.error.only_personal_can_have_project'
            );
            return $this->redirectToRoute("front_default");
        }

    }

    /**
     * @param BaseUser $user
     * @param $userInformationDTO
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createEditForm(BaseUser $user, $userInformationDTO)
    {
        $userForms = [
            ProApplicationUser::TYPE => ProUserInformationType::class,
            PersonalApplicationUser::TYPE => PersonalUserInformationType::class
        ];


        $userForm = $userForms[$user->getType()];

        return $this->formFactory->create(
            $userForm,
            $userInformationDTO
        );
    }

    /**
     * @param $projectDTO
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createProjectForm(ProjectDTO $projectDTO)
    {
        $availableMakes = $this->vehicleInfoIndexer->getVehicleInfoAggregatesFromMakeAndModel([]);

        $availableModels = [];
        if ($projectDTO->projectVehicles) {
            foreach ($projectDTO->projectVehicles as $projectVehicleDTO) {
                $availableModels[] = $this->vehicleInfoIndexer->getVehicleInfoAggregatesFromMakeAndModel($projectVehicleDTO->retrieveFilter());
            }
        }

        return $this->formFactory->create(
            ProjectType::class,
            $projectDTO,
            [
                'available_makes' => $availableMakes,
                'available_models' => $availableModels
            ]);
    }

    /**
     * @param Request $request
     * @param string $slug
     * @return Response
     * @throws \Exception
     */
    public function proUserViewInformationAction(Request $request, string $slug): Response
    {
        $user = $this->proUserRepository->findIgnoreSoftDeletedOneBy(['slug' => $slug]);

        if ($user->getDeletedAt() != null) {
            $response = $this->render('front/Exception/error410.html.twig', [
                'titleKey' => 'error_page.pro_user.deleted.title',
                'messageKey' => 'error_page.pro_user.deleted.body',
                'redirectionUrl' => $this->generateUrl('front_directory_view')
            ]);
            $response->setStatusCode(Response::HTTP_GONE);
            return $response;
        }

        if (!$user->canSeeMyProfile($this->getUser())) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_WARNING,
                'flash.warning.user.unauthorized_to_access_profile'
            );
            throw new AccessDeniedException();
        }

        $avatarForm = null;
        if ($user->is($this->getUser())) {
            $avatarForm = $this->createAvatarForm();
            $avatarForm->handleRequest($request);

            if ($avatarForm && $avatarForm->isSubmitted() && $avatarForm->isValid()) {
                $this->userEditionService->editInformations($this->getUser(), $avatarForm->getData());
                $this->eventBus->handle(new ProUserUpdated($this->getUser()));
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_INFO,
                    'flash.success.user.edit'
                );
                return $this->redirectToRoute('front_view_current_user_info');
            }
        }

        $searchForm = null;
        if (count($user->getVehicles()) > self::NB_VEHICLES_PER_PAGE) {
            $searchVehicleDTO = new SearchVehicleDTO();
            $searchForm = $this->formFactory->create(SearchVehicleType::class, $searchVehicleDTO, [
                'action' => $this->generateRoute('front_view_pro_user_info', ['slug' => $user->getSlug()]),
                'available_values' => [],
                'small_version' => true
            ]);
            $searchForm->handleRequest($request);
            $page = $request->query->get('page', 1);

            $garageIds = [];
            /** @var GarageProUser $garageMembership */
            foreach ($user->getEnabledGarageMemberships() as $garageMembership) {
                $garageIds[] = $garageMembership->getGarage()->getId();
            }
            $searchResultSet = $this->proVehicleEntityIndexer->getQueryGarageVehiclesResult($garageIds, $searchForm->get("text")->getData(), $page, self::NB_VEHICLES_PER_PAGE);
            if ($searchResultSet != null) {
                $vehicles = $this->proVehicleEditionService->getVehiclesBySearchResult($searchResultSet);
                $lastPage = ElasticUtils::numberOfPages($searchResultSet);
            } else {
                $vehicles = ['totalHits' => 0, 'hits' => []];
                $lastPage = 1;
            }
        } else {
            $userVehicles = $user->getVehicles();
            $vehicles = [
                'totalHits' => count($userVehicles),
                'hits' => $userVehicles
            ];
        }

        $addGarageForm = null;
        if ($this->getUser() instanceof ProUser && $user->is($this->getUser())) {
            $addGarageForm = $this->formFactory->create(GarageType::class, new GarageDTO(), [
                'only_google_fields' => true,
                'action' => $this->generateRoute('front_garage_create')]);
        }

        return $this->render('front/Seller/card.html.twig', [
            'avatarForm' => $avatarForm ? $avatarForm->createView() : null,
            'addGarageForm' => $addGarageForm ? $addGarageForm->createView() : null,
            'userIsMe' => $user->is($this->getUser()),
            'user' => $user,
            'isEditableByCurrentUser' => false,
            'searchForm' => $searchForm ? $searchForm->createView() : null,
            'vehicles' => $vehicles,
            'page' => $page ?? null,
            'lastPage' => $lastPage ?? null,
        ]);
    }

    /**
     * @Entity("user", expr="repository.findIgnoreSoftDeletedOneBy({'slug':slug})")
     * @param Request $request
     * @param string $slug
     * @return Response
     * @throws \Exception
     */
    public function personalUserViewInformationAction(Request $request, string $slug): Response
    {
        $user = $this->personalUserRepository->findIgnoreSoftDeletedOneBy(['slug' => $slug]);

        if ($user->getDeletedAt() != null) {
            if ($user->getCity() != null) {
                $redirectionUrl = $this->generateUrl('front_search_by_city', [
                    'city' => $user->getCity()->getPostalCode() . '-' . urlencode($user->getCity()->getName()),
                    'type' => SearchController::QP_TYPE_PERSONAL_PROJECT
                ]);
            } else {
                $redirectionUrl = $this->generateUrl('front_search', [
                    'type' => SearchController::QP_TYPE_PERSONAL_PROJECT
                ]);
            }
            $response = $this->render('front/Exception/error410.html.twig', [
                'titleKey' => 'error_page.personal_user.deleted.title',
                'messageKey' => 'error_page.personal_user.deleted.body',
                'redirectionUrl' => $redirectionUrl
            ]);
            $response->setStatusCode(Response::HTTP_GONE);
            return $response;
        }

        if (!$user->canSeeMyProfile($this->getUser())) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_WARNING,
                'flash.warning.user.unauthorized_to_access_profile'
            );
            throw new AccessDeniedException();
        }

        $avatarForm = null;
        if ($user->is($this->getUser())) {
            $avatarForm = $this->createAvatarForm();
            $avatarForm->handleRequest($request);
            if ($avatarForm && $avatarForm->isSubmitted() && $avatarForm->isValid()) {
                $this->userEditionService->editInformations($this->getUser(), $avatarForm->getData());
                $this->eventBus->handle(new PersonalUserUpdated($this->getUser()));

                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_INFO,
                    'flash.success.user.edit'
                );
                return $this->redirectToRoute('front_view_current_user_info');
            }
        }

        return $this->render('front/User/card.html.twig', [
            'avatarForm' => $avatarForm ? $avatarForm->createView() : null,
            'userIsMe' => $user->is($this->getUser()),
            'user' => $user
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function currentUserViewInformationAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $user = $this->getUser();

        if ($user instanceof ProUser) {
            return $this->proUserViewInformationAction($request, $user->getSlug());
        } else {
            return $this->personalUserViewInformationAction($request, $user->getSlug());
        }
    }

    /**
     * Legacy action for url : /profil/{id}
     * @param int $id
     * @return Response
     */
    public function legacyViewInformationAction(int $id): Response
    {
        $user = $this->userRepository->findIgnoreSoftDeleted($id);
        if (!$user || !$user instanceof BaseUser) {
            throw new NotFoundHttpException();
        }
        if ($user instanceof ProUser) {
            return $this->redirectToRoute('front_view_pro_user_info', ['slug' => $user->getSlug()], Response::HTTP_MOVED_PERMANENTLY);
        } else {
            return $this->redirectToRoute('front_view_personal_user_info', ['slug' => $user->getSlug()], Response::HTTP_MOVED_PERMANENTLY);
        }
    }

    /**
     * @return FormInterface
     */
    protected function createAvatarForm()
    {
        $userDTOs = [
            ProApplicationUser::TYPE => ProUserInformationDTO::class,
            PersonalApplicationUser::TYPE => UserInformationDTO::class
        ];
        /** @var UserInformationDTO $userInformationDTO */
        $userInformationDTO = new $userDTOs[$this->getUser()->getType()]($this->getUser());
        return $this->formFactory->create(
            UserAvatarType::class,
            $userInformationDTO
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function editPreferencesAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);

        $userPreferenceDTO = UserPreferencesDTO::createFromUser($this->getUser());
        $userPreferenceForm = $this->formFactory->create(UserPreferencesType::class, $userPreferenceDTO);
        $userPreferenceForm->handleRequest($request);
        if ($userPreferenceForm->isSubmitted() && $userPreferenceForm->isValid()) {

            $this->userEditionService->editPreferences($this->getUser(), $userPreferenceDTO);

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.user_preferences.edit'
            );

            return $this->redirectToRoute('front_user_edit_preferences');
        }

        return $this->render('front/Preferences/edit.html.twig', [
            'userPreferenceForm' => $userPreferenceForm->createView()
        ]);
    }

    /**
     * security.yml - access_control : ROLE_ADMIN only
     * @return Response
     */
    public function proUserslistAction()
    {
        $proUsers = $this->proUserRepository->findIgnoreSoftDeletedBy([], ['createdAt' => 'DESC']);

        return $this->render("front/adminContext/user/pro_user_list.html.twig", [
            'proUsers' => $proUsers
        ]);
    }

    /**
     * security.yml - access_control : ROLE_ADMIN only
     * @return Response
     */
    public function personalUserslistAction()
    {
        $personalUsers = $this->personalUserRepository->findIgnoreSoftDeletedBy([], ['createdAt' => 'DESC']);

        return $this->render("front/adminContext/user/personal_user_list.html.twig", [
            'personalUsers' => $personalUsers
        ]);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function deleteUserAction(Request $request, int $id)
    {
        $userToDelete = $this->userRepository->findIgnoreSoftDeleted($id);
        if (!$userToDelete || !$userToDelete instanceof BaseUser) {
            throw new NotFoundHttpException();
        }

        if (!$this->isGranted(UserVoter::DELETE, $userToDelete)) {
            $this->session->getFlashBag()->add(BaseController::FLASH_LEVEL_DANGER, 'flash.error.user.deletion_not_allowed');
            if ($request->headers->has(self::REQUEST_HEADER_REFERER)) {
                return $this->redirect($request->headers->get(self::REQUEST_HEADER_REFERER));
            } else {
                if ($userToDelete->isPro()) {
                    return $this->redirectToRoute('front_view_pro_user_info', ['slug' => $userToDelete->getSlug()]);
                } else {
                    return $this->redirectToRoute('front_view_personal_user_info', ['slug' => $userToDelete->getSlug()]);
                }
            }
        }

        $isUserHimSelf = $userToDelete->is($this->getUser());
        $isAlreadySoftDeleted = $userToDelete->getDeletedAt() != null;
        $resultMessages = $this->userEditionService->deleteUser($userToDelete, $this->getUser());
        foreach ($resultMessages['errorMessages'] as $errorMessage) {
            $this->session->getFlashBag()->add(BaseController::FLASH_LEVEL_WARNING, $errorMessage);
        }
        foreach ($resultMessages['successMessages'] as $garageId => $successMessage) {
            $this->session->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO, 'Garage (' . $garageId . ') : ' . $this->translator->trans($successMessage));
        }
        if (count($resultMessages['errorMessages']) == 0) {
            if ($isAlreadySoftDeleted) {
                $this->session->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO, 'flash.success.user.deleted.hard');
            } else {
                $this->session->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO, 'flash.success.user.deleted.soft');
            }
        }

        if ($isUserHimSelf) {
            // Manually logout the current User
            $this->tokenStorage->setToken(null);
            $this->session->invalidate();
            return $this->redirectToRoute('front_default');
        } elseif ($request->headers->has(self::REQUEST_HEADER_REFERER)) {
            return $this->redirect($request->headers->get(self::REQUEST_HEADER_REFERER));
        } else {
            if ($userToDelete->isPro()) {
                return $this->redirectToRoute('front_view_pro_user_info', ['slug' => $userToDelete->getSlug()]);
            } else {
                return $this->redirectToRoute('front_view_personal_user_info', ['slug' => $userToDelete->getSlug()]);
            }
        }
    }

    /**
     * @param Request $request
     * @param PersonalUser $personalUser
     * @return Response
     */
    public function convertPersonalToProAction(Request $request, PersonalUser $personalUser)
    {
        if(!$personalUser instanceof PersonalUser){
            $this->session->getFlashBag()->add(BaseController::FLASH_LEVEL_WARNING, "The user is not a PersonalUser");
        }else{
            $conversionResult = $this->userEditionService->convertPersonalToProUser($personalUser, $this->getUser());
            if($conversionResult['proUser'] instanceof ProUser){
                $this->session->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO, sprintf('ProUser id %d', $conversionResult['proUser']->getId()));
            }else {
                $this->session->getFlashBag()->add(BaseController::FLASH_LEVEL_WARNING, "Problème lors de la conversion");
            }
        }
        return $this->redirect($request->headers->get('referer'));
    }
}
