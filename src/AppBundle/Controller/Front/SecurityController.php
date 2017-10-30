<?php

namespace AppBundle\Controller\Front;

use AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository;
use AppBundle\Doctrine\Repository\DoctrineUserRepository;
use AppBundle\Form\DTO\PasswordLostDTO;
use AppBundle\Form\DTO\PasswordResetDTO;
use AppBundle\Form\DTO\RegistrationDTO;
use AppBundle\Form\Type\PasswordLostType;
use AppBundle\Form\Type\PasswordResetType;
use AppBundle\Form\Type\RegistrationType;
use AppBundle\Security\HasPasswordResettable;
use AppBundle\Security\Repository\RegisteredWithConfirmationProvider;
use AppBundle\Security\UserRegistrationService;
use AppBundle\Services\User\UserEditionService;
use AppBundle\Services\User\UserGlobalSearchService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Wamcar\User\Event\UserPasswordResetTokenGenerated;

class SecurityController extends BaseController
{
    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var UserRegistrationService  */
    protected $userRegistrationService;
    /** @var UserEditionService  */
    protected $userEditionService;
    /** @var  DoctrineUserRepository */
    private $userRepository;
    /** @var  DoctrinePersonalUserRepository */
    private $personalUserRepository;
    /** @var UserGlobalSearchService */
    private $userGlobalSearchService;
    /** @var AuthenticationUtils  */
    private $authenticationUtils;
    /** @var MessageBus */
    private $eventBus;

    /**
     * SecurityController constructor.
     * @param FormFactoryInterface $formFactory
     * @param UserRegistrationService $userRegistration
     * @param UserEditionService $userEditionService
     * @param DoctrineUserRepository $userRepository
     * @param DoctrinePersonalUserRepository $personalUserRepository
     * @param UserGlobalSearchService $userGlobalSearchService
     * @param AuthenticationUtils $authenticationUtils
     * @param MessageBus $eventBus
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UserRegistrationService $userRegistration,
        UserEditionService $userEditionService,
        DoctrineUserRepository $userRepository,
        DoctrinePersonalUserRepository $personalUserRepository,
        UserGlobalSearchService $userGlobalSearchService,
        AuthenticationUtils $authenticationUtils,
        MessageBus $eventBus
    )
    {
        $this->formFactory = $formFactory;
        $this->userRegistrationService = $userRegistration;
        $this->userEditionService = $userEditionService;
        $this->userRepository = $userRepository;
        $this->personalUserRepository = $personalUserRepository;
        $this->userGlobalSearchService = $userGlobalSearchService;
        $this->authenticationUtils = $authenticationUtils;
        $this->eventBus = $eventBus;
    }

    /**
     * @param Request $request
     * @param string $type
     * @return Response
     */
    public function registerAction(Request $request, string $type): Response
    {
        $registrationForm = $this->formFactory->create(RegistrationType::class);
        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            try {
                /** @var RegistrationDTO $registrationDTO */
                $registrationDTO = $registrationForm->getData();
                $registrationDTO->type = $type;
                $this->userRegistrationService->registerUser($registrationDTO);
            } catch (UniqueConstraintViolationException $exception) {
                $this->session->getFlashBag()->add(
                    'flash.danger.registration_duplicate',
                    self::FLASH_LEVEL_DANGER
                );

                return $this->render(sprintf('front/Security/Register/user_%s.html.twig', $type), [
                    'form' => $registrationForm->createView(),
                ]);
            }

            $this->session->getFlashBag()->add(
                'flash.success.registration_success',
                self::FLASH_LEVEL_INFO
            );

            return $this->redirectToRoute('front_default');
        }

        return $this->render(sprintf('front/Security/Register/user_%s.html.twig', $type), [
            'form' => $registrationForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param $token
     * @return Response
     * @throws \Exception
     */
    public function confirmRegistrationAction(Request $request, $token): Response
    {
        if (!$this->personalUserRepository instanceof RegisteredWithConfirmationProvider) {
            throw new \Exception('UserRepository must implement "RegisteredWithConfirmationProvider" to be able to confirm registration');
        }

        $user = $this->personalUserRepository->findOneByRegistrationToken($token);

        if (!$user) {
            $this->session->getFlashBag()->add(
                'flash.danger.token_invalid',
                self::FLASH_LEVEL_DANGER
            );

            return $this->redirectToRoute('security_login');
        }

        $this->userRegistrationService->confirmUserRegistration($user);

        $this->session->getFlashBag()->add(
            'flash.success.registration_confirmed',
            self::FLASH_LEVEL_INFO
        );
        // redirect to login page, to allow user to enter his credentials
        return $this->redirectToRoute('front_default');
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function loginAction(Request $request): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        if ($error) {
            $this->session->getFlashBag()->add(
                $error->getMessage(),
                self::FLASH_LEVEL_DANGER
            );
            return $this->redirectToRoute('front_default');
        }

        return $this->render('front/User/includes/form_login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function passwordLostAction(Request $request): Response
    {
        $email = $request->get('email', null);
        if(!$email) {
            return $this->render('front/User/includes/form_password_lost.html.twig');
        }

        /** @var HasPasswordResettable $user */
        $user = $this->userRepository->findOneByEmail($email);
        if(!$user) {
            $this->session->getFlashBag()->add(
                'flash.error.user_no_exist',
                self::FLASH_LEVEL_DANGER
            );
            return $this->redirectToRoute('front_default');
        }

        $user = $this->userEditionService->generatePasswordResetToken($user);

        // a mail will be send to the user on the event handling
        $this->eventBus->handle(new UserPasswordResetTokenGenerated($user));

        $this->session->getFlashBag()->add(
            'flash.success.reset_password_success',
            self::FLASH_LEVEL_INFO
        );

        return $this->redirectToRoute('front_default');
    }

    /**
     * @param Request $request
     * @param $token
     * @return Response
     * @throws \Exception
     */
    public function passwordLostResetAction(Request $request, $token): Response
    {
        /** @var HasPasswordResettable $user */
        $user = $this->userGlobalSearchService->findOneByPasswordResetToken($token);
        if(!$user) {
            $this->session->getFlashBag()->add(
                'flash.error.user_no_exist',
                self::FLASH_LEVEL_DANGER
            );
            return $this->redirectToRoute('front_default');
        }

        $form = $this->formFactory->create(PasswordResetType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PasswordResetDTO $passwordResetDTO */
            $passwordResetDTO = $form->getData();
            $this->userEditionService->editPassword($user, $passwordResetDTO->password);

            $this->session->getFlashBag()->add(
                'flash.success.password_changed',
                self::FLASH_LEVEL_INFO
            );
            return $this->redirectToRoute('front_default');

        }

        return $this->render('front/Security/ResetPassword/reset.html.twig', [
            'form' => $form->createView(),
            'token' => $token
        ]);
    }
}
