<?php

namespace AppBundle\Controller\Front;

use AppBundle\Controller\Front\PersonalContext\RegistrationController;
use AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository;
use AppBundle\Doctrine\Repository\DoctrineUserRepository;
use AppBundle\Form\DTO\RegistrationDTO;
use AppBundle\Form\Type\PasswordResetType;
use AppBundle\Form\Type\RegistrationType;
use AppBundle\Security\HasPasswordResettable;
use AppBundle\Security\Repository\RegisteredWithConfirmationProvider;
use AppBundle\Security\UserAuthenticator;
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
    /** @var UserRegistrationService */
    protected $userRegistrationService;
    /** @var UserAuthenticator */
    protected $userAuthenticator;
    /** @var UserEditionService */
    protected $userEditionService;
    /** @var  DoctrineUserRepository */
    private $userRepository;
    /** @var  DoctrinePersonalUserRepository */
    private $personalUserRepository;
    /** @var UserGlobalSearchService */
    private $userGlobalSearchService;
    /** @var AuthenticationUtils */
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
        UserAuthenticator $userAuthenticator,
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
        $this->userAuthenticator = $userAuthenticator;
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
                $registeredUser = $this->userRegistrationService->registerUser($registrationDTO);
            } catch (UniqueConstraintViolationException $exception) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_DANGER,
                    'flash.danger.registration_duplicate'
                );

                return $this->render(sprintf('front/Security/Register/user_%s.html.twig', $type), [
                    'form' => $registrationForm->createView(),
                ]);
            }

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.registration_success_' . $type
            );

            if ($registeredUser->hasConfirmedRegistration()) {
                $this->userAuthenticator->authenticate($registeredUser);

                // Check if target_path in session due to a previous AccessDeniedException
                $key = sprintf('_security.%s.target_path', $this->tokenStorage->getToken()->getProviderKey());
                if ($redirectTo = $this->session->get($key)) {
                    $this->session->remove($key);
                    return $this->redirect($redirectTo);
                } else {
                    return $this->redirectToRoute('front_default');
                }
            }
            return $this->redirectToRoute('register_confirm');
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
                self::FLASH_LEVEL_DANGER,
                'flash.danger.token_invalid'
            );

            return $this->redirectToRoute('security_login_page');
        }

        $this->userRegistrationService->confirmUserRegistration($user);

        if ($user->hasConfirmedRegistration()) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.registration_confirmed'
            );

            $this->userAuthenticator->authenticate($user);

            // Check if target_path in session due to a previous AccessDeniedException
            $key = sprintf('_security.%s.target_path', $this->tokenStorage->getToken()->getProviderKey());
            if ($redirectTo = $this->session->get($key)) {
                $this->session->remove($key);
                return $this->redirect($redirectTo);
            } else {
                $vehicleReplace = $request->get(RegistrationController::VEHICLE_REPLACE_PARAM);
                return $vehicleReplace ? $this->redirectToRoute('front_edit_user_info_tab', ['tab' => 'project']) : $this->redirectToRoute('front_default');
            }
        }

        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_DANGER,
            'flash.error.registration_unconfirmed'
        );
        return $this->redirectToRoute('front_default');
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function loginAction(Request $request): Response
    {

        return $this->render('front/User/includes/form_login.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function loginPageAction(Request $request): Response
    {
        if ($this->isUserAuthenticated()) {
            return $this->redirectToRoute('front_view_current_user_info');
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        if ($error) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_DANGER,
                $error->getMessage()
            );
            return $this->redirectToRoute('security_login_page');
        }

        return $this->render('front/Security/Login/login.html.twig', [
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function passwordLostAction(Request $request): Response
    {
        $email = $request->get('email', null);
        if (!$email) {
            return $this->render('front/User/includes/form_password_lost.html.twig');
        }

        /** @var HasPasswordResettable $user */
        $user = $this->userRepository->findOneByEmail($email);
        if (!$user) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_DANGER,
                'flash.error.user_no_exist'
            );
            return $this->redirectToRoute('security_login_page');
        }

        $user = $this->userEditionService->generatePasswordResetToken($user);

        // a mail will be send to the user on the event handling
        $this->eventBus->handle(new UserPasswordResetTokenGenerated($user));

        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_INFO,
            'flash.success.reset_password_success'
        );

        return $this->redirectToRoute('security_login_page');
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
        if (!$user) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_DANGER,
                'flash.error.user_no_exist'
            );
            return $this->redirectToRoute('front_default');
        }

        $form = $this->formFactory->create(PasswordResetType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userEditionService->editPassword($user, $form->get('password')->getData());

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.password_changed'
            );
            return $this->redirectToRoute('front_default');

        }

        return $this->render('front/Security/ResetPassword/reset.html.twig', [
            'form' => $form->createView(),
            'token' => $token
        ]);
    }
}
