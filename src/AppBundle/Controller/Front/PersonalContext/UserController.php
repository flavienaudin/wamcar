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
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wamcar\User\BaseUser;
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

    /**
     * SecurityController constructor.
     * @param FormFactoryInterface $formFactory
     * @param UserRepository $userRepository
     * @param UserEditionService $userEditionService
     * @param VehicleInfoAggregator $vehicleInfoAggregator
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UserRepository $userRepository,
        UserEditionService $userEditionService,
        VehicleInfoAggregator $vehicleInfoAggregator
    )
    {
        $this->formFactory = $formFactory;
        $this->userRepository = $userRepository;
        $this->userEditionService = $userEditionService;
        $this->vehicleInfoAggregator = $vehicleInfoAggregator;
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

        $userForms = [
            ProApplicationUser::TYPE => ProUserInformationType::class,
            PersonalApplicationUser::TYPE => UserInformationType::class
        ];
        $userDTOs = [
            ProApplicationUser::TYPE => ProUserInformationDTO::class,
            PersonalApplicationUser::TYPE => UserInformationDTO::class
        ];
        $userProfileTemplate = [
            ProApplicationUser::TYPE => 'front/Seller/edit.html.twig',
            PersonalApplicationUser::TYPE => 'front/User/edit.html.twig',
        ];


        $userForm = $userForms[$user->getType()];
        /** @var UserInformationDTO $userInformationDTO */
        $userInformationDTO = new $userDTOs[$user->getType()]($user);

        $editForm = $this->formFactory->create(
            $userForm,
            $userInformationDTO
        );

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->userEditionService->editInformations($user, $userInformationDTO);

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.user_edit'
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
    public function projectAction(Request $request): Response
    {
        /** @var ApplicationUser $user */
        $user = $this->getUser();

        $filters = [];
        $availableValues = $this->vehicleInfoAggregator->getVehicleInfoAggregatesFromMakeAndModel($filters);

        $projectDTO = new ProjectDTO();

        $projectForm = $this->formFactory->create(
            ProjectType::class,
            $projectDTO,
            ['available_values' => $availableValues]);

        $projectForm->handleRequest($request);
        if ($projectForm->isSubmitted() && $projectForm->isValid()) {
            $this->userEditionService->projectInformations($user, $projectDTO);

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.user_edit'
            );
        }


        return $this->render('front/User/edit.html.twig', [
            'projectForm' => $projectForm->createView(),
            'user' => $user
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
