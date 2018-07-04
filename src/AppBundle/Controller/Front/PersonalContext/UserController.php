<?php

namespace AppBundle\Controller\Front\PersonalContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository;
use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\DTO\ProjectDTO;
use AppBundle\Form\DTO\ProUserInformationDTO;
use AppBundle\Form\DTO\UserInformationDTO;
use AppBundle\Form\Type\GarageType;
use AppBundle\Form\Type\ProjectType;
use AppBundle\Form\Type\ProUserInformationType;
use AppBundle\Form\Type\UserAvatarType;
use AppBundle\Form\Type\UserInformationType;
use AppBundle\Services\Garage\GarageEditionService;
use AppBundle\Services\User\UserEditionService;
use AppBundle\Utils\VehicleInfoAggregator;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\PersonalUserUpdated;
use Wamcar\User\Event\ProUserUpdated;
use Wamcar\User\PersonalUser;
use Wamcar\User\Project;
use Wamcar\User\ProUser;
use Wamcar\User\UserRepository;

class UserController extends BaseController
{
    const TAB_PROFILE = 'profile';
    const TAB_PROJECT = 'project';

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

    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;

    /** @var MessageBus */
    protected $eventBus;

    /**
     * SecurityController constructor.
     * @param FormFactoryInterface $formFactory
     * @param UserRepository $userRepository
     * @param DoctrinePersonalUserRepository $userRepository
     * @param DoctrinePersonalUserRepository $personalUserRepository ,
     * @param DoctrineProUserRepository $proUserRepository ,
     * @param UserRepository $userRepository
     * @param UserEditionService $userEditionService
     * @param GarageEditionService $garageEditionService
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     * @param MessageBus $eventBus
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UserRepository $userRepository,
        DoctrinePersonalUserRepository $personalUserRepository,
        DoctrineProUserRepository $proUserRepository,
        UserEditionService $userEditionService,
        GarageEditionService $garageEditionService,
        VehicleInfoAggregator $vehicleInfoAggregator,
        MessageBus $eventBus
    )
    {
        $this->formFactory = $formFactory;
        $this->userRepository = $userRepository;
        $this->personalUserRepository = $personalUserRepository;
        $this->proUserRepository = $proUserRepository;
        $this->userEditionService = $userEditionService;
        $this->garageEditionService = $garageEditionService;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->eventBus = $eventBus;
    }

    /**
     * @param Request $request
     * @param string $tab {self::TAB_PROFILE,self::TAB_PROJECT}
     * @return Response
     * @throws \Exception
     */
    public function editInformationsAction(Request $request, $tab = self::TAB_PROFILE): Response
    {
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

        if ($user instanceof PersonalUser) {
            if ($user->getProject() === null) {
                $user->setProject(new Project($user));
            }
            $projectDTO = ProjectDTO::buildFromProject($user->getProject());
            $projectForm = $this->createProjectForm($projectDTO);
            $projectForm->handleRequest($request);
        } else {
            $projectForm = null;
        }

        $editForm = $this->createEditForm($user, $userInformationDTO);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->userEditionService->editInformations($user, $userInformationDTO);
            if ($this->getUser()->getType() === PersonalUser::TYPE) {
                $this->eventBus->handle(new PersonalUserUpdated($user));
            } else {
                $this->eventBus->handle(new ProUserUpdated($user));
            }

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.user_edit'
            );

            return $this->redirectToRoute('front_view_current_user_info');
        }

        if ($projectForm && $projectForm->isSubmitted() && $projectForm->isValid()) {
            $this->userEditionService->projectInformations($user, $projectDTO);
            $this->eventBus->handle(new PersonalUserUpdated($user));

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.user_edit'
            );

            return $this->redirectToRoute('front_view_current_user_info');
        }

        return $this->render($userProfileTemplate[$user->getType()], [
            'editUserForm' => $editForm->createView(),
            'projectForm' => $projectForm ? $projectForm->createView() : null,
            'user' => $user,
            'tab' => $tab
        ]);
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
            PersonalApplicationUser::TYPE => UserInformationType::class
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
        $availableMakes = $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel([]);

        $availableModels = [];
        if ($projectDTO->projectVehicles) {
            foreach ($projectDTO->projectVehicles as $projectVehicleDTO) {
                $availableModels[] = $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel($projectVehicleDTO->retrieveFilter());
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
     * @param null|int $id
     * @return Response
     * @throws \Exception
     */
    public function viewInformationAction(Request $request, $id = null): Response
    {
        $user = $id ? $this->userRepository->find($id) : $this->getUser();
        if (!$user || !$user instanceof BaseUser) {
            throw new NotFoundHttpException();
        }

        if (!$user->canSeeMyProfile($this->getUser())) {

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_WARNING,
                'flash.warning.user.unauthorized_to_access_profile'
            );
            throw new AccessDeniedException();
        }

        $templates = [
            ProUser::TYPE => 'front/Seller/card.html.twig',
            PersonalUser::TYPE => 'front/User/card.html.twig',
        ];

        if (!$templates[$user->getType()]) {
            throw new NotFoundHttpException();
        }

        $avatarForm = null;
        if ($user->is($this->getUser())) {
            $avatarForm = $this->createAvatarForm();
            $avatarForm->handleRequest($request);

            if ($avatarForm && $avatarForm->isSubmitted() && $avatarForm->isValid()) {
                $this->userEditionService->editInformations($this->getUser(), $avatarForm->getData());
                if ($this->getUser() === PersonalUser::TYPE) {
                    $this->eventBus->handle(new PersonalUserUpdated($this->getUser()));
                } else {
                    $this->eventBus->handle(new ProUserUpdated($this->getUser()));
                }

                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_INFO,
                    'flash.success.user_edit'
                );

                return $this->redirectToRoute('front_view_current_user_info');
            }
        }

        $addGarageForm = null;
        if ($user instanceof ProUser && $user->getGarage() == null) {
            $garageDTO = new GarageDTO();
            $addGarageForm = $this->formFactory->create(GarageType::class, $garageDTO, ['only_google_fields' => true]);
            $addGarageForm->handleRequest($request);
            if ($addGarageForm->isSubmitted()) {
                if ($addGarageForm->isValid()) {
                    $this->garageEditionService->editInformations($garageDTO, null, $this->getUser());
                    $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.garage_create');
                    return $this->redirectToRoute('front_view_current_user_info');
                } else {
                    return $this->redirectToRoute('front_garage_create');
                }
            }
        }


        return $this->render($templates[$user->getType()], [
            'avatarForm' => $avatarForm ? $avatarForm->createView() : null,
            'addGarageForm' => $addGarageForm ? $addGarageForm->createView() : null,
            'userIsMe' => $user->is($this->getUser()),
            'user' => $user
        ]);
    }

    /**
     * @param Request $request
     */
    public function listAction(Request $request)
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException('Only admin can access user listing');
        }

        $personalUsers = $this->personalUserRepository->findBy([], ['createdAt' => 'DESC']);

        $proUsers = $this->proUserRepository->findBy([], ['createdAt' => 'DESC']);


        return $this->render("front/adminContext/user/user_list.html.twig", [
            'personalUsers' => $personalUsers,
            'proUsers' => $proUsers
        ]);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
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
}
