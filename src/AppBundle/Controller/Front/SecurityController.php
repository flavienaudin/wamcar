<?php

namespace AppBundle\Controller\Front;

use AppBundle\Form\Type\RegistrationType;
use AppBundle\Security\UserRegistrationService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\User\Context;

class SecurityController extends BaseController
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var UserRegistrationService  */
    protected $userRegistrationService;

    /**
     * SecurityController constructor.
     * @param FormFactoryInterface $formFactory
     * @param UserRegistrationService $userRegistration
     */
    public function __construct(FormFactoryInterface $formFactory, UserRegistrationService $userRegistration)
    {
        $this->formFactory = $formFactory;
        $this->userRegistrationService = $userRegistration;
    }

    /**
     * @param Request $request
     * @param $context
     * @return Response
     */
    public function registerAction(Request $request, $context): Response
    {
         $context = new Context($context);

        $registrationForm = $this->formFactory->create(RegistrationType::class);

        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            try {
                $this->userRegistrationService->registerUser($registrationForm->getData(), $context);
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
}
