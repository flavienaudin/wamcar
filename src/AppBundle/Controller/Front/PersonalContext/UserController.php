<?php

namespace AppBundle\Controller\Front\PersonalContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Controller\Front\ProContext\SearchController;
use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository;
use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Elasticsearch\Elastica\VehicleInfoEntityIndexer;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\DTO\ProjectDTO;
use AppBundle\Form\DTO\ProUserInformationDTO;
use AppBundle\Form\DTO\UserInformationDTO;
use AppBundle\Form\DTO\UserPreferencesDTO;
use AppBundle\Form\Type\GarageType;
use AppBundle\Form\Type\PersonalUserInformationType;
use AppBundle\Form\Type\ProjectType;
use AppBundle\Form\Type\ProUserInformationType;
use AppBundle\Form\Type\UserAvatarType;
use AppBundle\Form\Type\UserPreferencesType;
use AppBundle\Services\Affinity\AffinityAnswerCalculationService;
use AppBundle\Services\Garage\GarageEditionService;
use AppBundle\Services\User\UserEditionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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

    /** @var MessageBus */
    protected $eventBus;

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
     * @param MessageBus $eventBus
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
        MessageBus $eventBus,
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
        $this->eventBus = $eventBus;
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
            'user' => $user
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
    public function listAction()
    {
        $personalUsers = $this->personalUserRepository->findIgnoreSoftDeletedBy([], ['createdAt' => 'DESC']);
        $proUsers = $this->proUserRepository->findIgnoreSoftDeletedBy([], ['createdAt' => 'DESC']);

        return $this->render("front/adminContext/user/user_list.html.twig", [
            'personalUsers' => $personalUsers,
            'proUsers' => $proUsers
        ]);
    }


    /**
     * security.yml - access_control : ROLE_ADMIN only
     * @param BaseUser $userToDelete
     * @param bool $forGood
     * @return Response
     */
    public function deleteUserAction(Request $request, BaseUser $userToDelete)
    {
        $isPro = $userToDelete instanceof ProUser;
        $forGood = $request->query->get("forGood", false);
        // TODO check all associations data
        // $this->userEditionService->deleteUser($userToDelete, $forGood);
        if ($forGood) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.user.deleted_for_good'
            );
        } else {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.user.deleted'
            );
        }

        return $this->redirectToRoute('admin_user_list', [
            '_fragment' => $isPro ? 'pro-user-panel' : 'personal-user-panel'
        ]);
    }
}
