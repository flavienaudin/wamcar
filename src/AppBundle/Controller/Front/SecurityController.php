<?php

namespace AppBundle\Controller\Front;

use AppBundle\Form\DTO\RegistrationDTO;
use AppBundle\Form\Type\RegistrationType;
use AppBundle\Security\Repository\RegisteredWithConfirmationProvider;
use AppBundle\Security\ShouldConfirmRegistration;
use AppBundle\Security\UserRegistrationService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\User\UserRepository;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends BaseController
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var UserRegistrationService  */
    protected $userRegistrationService;

    /** @var  UserRepository */
    private $userRepository;

    /** @var AuthenticationUtils  */
    private $authenticationUtils;


    /**
     * SecurityController constructor.
     * @param FormFactoryInterface $formFactory
     * @param UserRegistrationService $userRegistration
     * @param UserRepository $userRepository,
     * @param AuthenticationUtils $authenticationUtils
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        UserRegistrationService $userRegistration,
        UserRepository $userRepository,
        AuthenticationUtils $authenticationUtils
    )
    {
        $this->formFactory = $formFactory;
        $this->userRegistrationService = $userRegistration;
        $this->userRepository = $userRepository;
        $this->authenticationUtils = $authenticationUtils;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function registerAction(Request $request): Response
    {

        $registrationForm = $this->formFactory->create(RegistrationType::class);

        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            try {
                /** @var RegistrationDTO $registrationDTO */
                $registrationDTO = $registrationForm->getData();
                $registrationDTO->type = 'personal';
                $this->userRegistrationService->registerUser($registrationDTO);
            } catch (UniqueConstraintViolationException $exception) {
                $this->session->getFlashBag()->add(
                    'flash.danger.registration_duplicate',
                    self::FLASH_LEVEL_DANGER
                );

                return $this->render('front/Security/register.html.twig', [
                    'form' => $registrationForm->createView(),
                ]);
            }

            $this->session->getFlashBag()->add(
                'flash.success.registration_success',
                self::FLASH_LEVEL_INFO
            );

            return $this->redirectToRoute('front_default');
        }

        return $this->render('front/Security/register.html.twig', [
            'form' => $registrationForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function registerProAction(Request $request): Response
    {

        $registrationForm = $this->formFactory->create(RegistrationType::class);

        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            try {
                /** @var RegistrationDTO $registrationDTO */
                $registrationDTO = $registrationForm->getData();
                $registrationDTO->type = 'pro';
                $this->userRegistrationService->registerUser($registrationDTO);
            } catch (UniqueConstraintViolationException $exception) {
                $this->session->getFlashBag()->add(
                    'flash.danger.registration_duplicate',
                    self::FLASH_LEVEL_DANGER
                );

                return $this->render('front/Security/register.html.twig', [
                    'form' => $registrationForm->createView(),
                ]);
            }

            $this->session->getFlashBag()->add(
                'flash.success.registration_success',
                self::FLASH_LEVEL_INFO
            );

            return $this->redirectToRoute('front_default');
        }

        return $this->render('front/Security/register.html.twig', [
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
        if (!$this->userRepository instanceof RegisteredWithConfirmationProvider) {
            throw new \Exception('UserRepository must implement "RegisteredWithConfirmationProvider" to be able to confirm registration');
        }

        $user = $this->userRepository->findOneByRegistrationToken($token);

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
        }

        return $this->render('front/Security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

}
