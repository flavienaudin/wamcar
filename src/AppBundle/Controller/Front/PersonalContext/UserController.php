<?php

namespace AppBundle\Controller\Front\PersonalContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Form\DTO\ProjectDTO;
use AppBundle\Form\DTO\ProUserInformationDTO;
use AppBundle\Form\DTO\UserInformationDTO;
use AppBundle\Form\Type\ProjectType;
use AppBundle\Form\Type\ProUserInformationType;
use AppBundle\Form\Type\UserInformationType;
use AppBundle\Services\User\UserEditionService;
use AppBundle\Utils\VehicleInfoAggregator;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\PersonalUserUpdated;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;
use Wamcar\User\UserRepository;

class UserController extends BaseController
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var UserRepository */
    protected $userRepository;

    /** @var UserEditionService */
    protected $userEditionService;

    /** @var VehicleInfoAggregator */
    private $vehicleInfoAggregator;

    /** @var MessageBus */
    protected $eventBus;

    /**
     * SecurityController constructor.
     * @param FormFactoryInterface $formFactory
     * @param UserRepository $userRepository
     * @param UserEditionService $userEditionService
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     * @param MessageBus $eventBus
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UserRepository $userRepository,
        UserEditionService $userEditionService,
        VehicleInfoAggregator $vehicleInfoAggregator,
        MessageBus $eventBus
    )
    {
        $this->formFactory = $formFactory;
        $this->userRepository = $userRepository;
        $this->userEditionService = $userEditionService;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
        $this->eventBus = $eventBus;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function editInformationsAction(Request $request): Response
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

        if ($user instanceof PersonalUser && $user->getProject()) {
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
            $this->eventBus->handle(new PersonalUserUpdated($user));

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
            'projectForm' => !$projectForm?:$projectForm->createView(),
            'user' => $user
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

        if(!$user->canSeeMyProfile($this->getUser())){
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_WARNING,
                'flash.warning.user.unauthorized_to_access_profile'
            );
            return $this->redirectToRoute('front_default');
        }


        $templates = [
            ProUser::TYPE => 'front/Seller/card.html.twig',
            PersonalUser::TYPE => 'front/User/card.html.twig',
        ];

        if (!$templates[$user->getType()]) {
            throw new NotFoundHttpException();
        }

        return $this->render($templates[$user->getType()], [
            'userIsMe' => $user->is($this->getUser()),
            'user' => $user
        ]);
    }
}
