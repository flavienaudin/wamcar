<?php

namespace AppBundle\Controller\Front\PersonalContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Repository\DoctrineUserRepository;
use AppBundle\Form\DTO\UserInformationDTO;
use AppBundle\Form\Type\UserInformationType;
use AppBundle\Security\UserEditionService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var DoctrineUserRepository  */
    protected $doctrineUserRepository;

    /** @var UserEditionService  */
    protected $userEditionService;

    /**
     * SecurityController constructor.
     * @param FormFactoryInterface $formFactory
     * @param DoctrineUserRepository $doctrineUserRepository
     * @param UserEditionService $userEditionService
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        DoctrineUserRepository $doctrineUserRepository,
        UserEditionService $userEditionService
    )
    {
        $this->formFactory = $formFactory;
        $this->doctrineUserRepository = $doctrineUserRepository;
        $this->userEditionService = $userEditionService;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function userInformationsAction(Request $request): Response
    {
        //TODO : Récupérer le user courant quand dispo
        $user = $this->doctrineUserRepository->findOneByEmail('fabien@novaway.fr');

        $userInformationDTO = new UserInformationDTO($user);

        $editForm = $this->formFactory->create(
            UserInformationType::class,
            $userInformationDTO
        );

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->userEditionService->editInformations($userInformationDTO);

            $this->session->getFlashBag()->add(
                'flash.success.user_edit',
                self::FLASH_LEVEL_INFO
            );
        }


        return $this->render('front/User/personal_informations.html.twig', [
            'form' => $editForm->createView()
        ]);
    }
}
