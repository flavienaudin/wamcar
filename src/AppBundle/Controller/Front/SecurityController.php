<?php

namespace AppBundle\Controller;


use AppBundle\Controller\Front\BaseController;
use AppBundle\DTO\Form\RegistrationData;
use AppBundle\Entity\AbleToLogin;
use AppBundle\Entity\ApplicationUser;
use AppBundle\Form\Registration;
use AppBundle\Security\UserRegistrationService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Wamcar\User\User;
use Wamcar\User\UserRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends BaseController
{
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var UserRegistrationService  */
    private $userRegistrationService;
    /** @var AuthenticationUtils  */
    private $authenticationUtils;
    /** @var  UserRepository */
    private $userRepository;

    public function __construct(
        FormFactoryInterface $formFactory,
        UserRegistrationService $userRegistrationService,
        AuthenticationUtils $authenticationUtils,
        UserRepository $userRepository
    )
    {
        $this->formFactory = $formFactory;
        $this->userRegistrationService = $userRegistrationService;
        $this->authenticationUtils = $authenticationUtils;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function registerAction(Request $request): Response
    {
        $registrationForm = $this->formFactory->create(
            Registration::class,
            new RegistrationData($request->getClientIp(), $request->get('_username'))
        );

        $registrationForm->handleRequest($request);
        if ($registrationForm->isValid()) {
            try {
                $this->userRegistrationService->registerUser($registrationForm->getData());
            } catch (UniqueConstraintViolationException $exception) {
                $this->addFlash(
                    'flash.danger.registration_duplicate',
                    self::FLASH_LEVEL_DANGER
                );

                return $this->templatingEngine->renderResponse('Security/Register/register.html.twig', [
                    'form' => $registrationForm->createView(),
                ]);
            }

            $this->addFlash(
                'flash.success.registration_success',
                self::FLASH_LEVEL_INFO
            );

            return $this->redirectToRoute('homepage');
        }

        return $this->templatingEngine->renderResponse('Security/Register/register.html.twig', [
            'form' => $registrationForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function loginAction(Request $request): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();

        if ($error) {
            $this->addFlash(
                $error->getMessage(),
                self::FLASH_LEVEL_DANGER
            );
        }

        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->templatingEngine->renderResponse('Security/Login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function passwordLostAction(Request $request): Response
    {
        if ($email = $request->get('email')) {

            $user = $this->userRepository->findOneByEmail($email);

            if (!$user) {
                $user = $this->shopRepository->findOneByEmail($email);
                if (!$user) {
                    $this->addFlash(
                        'flash.danger.email_not_found',
                        self::FLASH_LEVEL_DANGER
                    );
                    return $this->templatingEngine->renderResponse('Security/PasswordLost/passwordLost.html.twig');
                }
            }

            /** @var AbleToLogin $user */
            if ($user instanceof User) {
                $user = $this->userEditionService->generatePasswordResetToken($user);
                // a mail will be send to the user on the event handling
                $this->eventBus->handle(new UserPasswordResetTokenGenerated($user));
            } else {
                $user = $this->shopPasswordResetService->generatePasswordResetToken($user);
                // a mail will be send to the shop on the event handling
                $this->eventBus->handle(new ShopPasswordResetTokenGenerated($user));
            }

            $this->addFlash(
                'flash.success.reset_password_success',
                self::FLASH_LEVEL_INFO
            );
        }

        return $this->templatingEngine->renderResponse('Security/PasswordLost/passwordLost.html.twig');
    }

    /**
     * @param Request $request
     * @param $token
     * @return Response
     * @throws \Exception
     */
    public function passwordLostResetAction(Request $request, $token): Response
    {
        if (!$this->userRepository instanceof PasswordResettable) {
            throw new \Exception('UserRepository must implement "PasswordResettable" to be able to reset password');
        }

        if (!$this->shopRepository instanceof PasswordResettable) {
            throw new \Exception('ShopRepository must implement "PasswordResettable" to be able to reset password');
        }

        $user = $this->userRepository->findOneByPasswordResetToken($token);

        if (!$user) {
            // if no user found, it could be a store
            $user = $this->shopRepository->findOneByPasswordResetToken($token);
            if (!$user) {
                $this->addFlash(
                    'flash.danger.token_invalid',
                    self::FLASH_LEVEL_DANGER,
                    [],
                    'common'
                );

                return $this->redirectToRoute('security_login');
            }
        }

        if ($user instanceof ApplicationUser) {
            $editForm = $this->formFactory->create(
                ResetPassword::class,
                new UserPasswordEditData($user)
            );
        } else {
            $editForm = $this->formFactory->create(
                ResetPassword::class,
                new ShopPasswordEditData($user)
            );
        }


        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            if ($user instanceof ApplicationUser) {
                // when editing the user, the passwordResetToken will be reset
                $user = $this->userEditionService->editPassword($editForm->getData());
            } else {
                // when editing the user, the passwordResetToken will be reset
                $user = $this->shopPasswordResetService->editPassword($editForm->getData());
            }

            // and authenticate the user
            $this->authenticationService->authenticate($user);

            $this->addFlash(
                'flash.success.password_changed',
                self::FLASH_LEVEL_INFO
            );

            return $this->redirectToRoute($this->defaultTargetPath);
        }

        return $this->templatingEngine->renderResponse('Security/PasswordLost/reset.html.twig', [
            'form' => $editForm->createView(),
            'token' => $token
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
        if (!$this->userRepository instanceof RegistrationTokenable) {
            throw new \Exception('UserRepository must implement "RegistrationTokenable" to be able to confirm registration');
        }

        $user = $this->userRepository->findOneByRegistrationToken($token);

        if (!$user) {
            $this->addFlash(
                'flash.danger.token_invalid',
                self::FLASH_LEVEL_DANGER,
                [],
                'common'
            );

            return $this->redirectToRoute('security_login');
        }

        $this->userRegistrationService->confirmUserRegistration($user);

        $this->addFlash(
            'flash.success.registration_confirmed',
            self::FLASH_LEVEL_INFO
        );
        // redirect to login page, to allow user to enter his credentials
        return $this->redirectToRoute('security_login');
    }
}
