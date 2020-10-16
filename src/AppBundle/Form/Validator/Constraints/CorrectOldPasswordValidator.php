<?php

namespace AppBundle\Form\Validator\Constraints;


use AppBundle\Doctrine\Entity\ApplicationUser;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CorrectOldPasswordValidator extends ConstraintValidator
{


    /** @var PasswordEncoderInterface */
    private $passwordEncoder;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var TranslatorInterface */
    private $translation;


    public function __construct(PasswordEncoderInterface $passwordEncoder, TokenStorageInterface $tokenStorage, TranslatorInterface $translation, SessionInterface $session, UrlGeneratorInterface $router)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenStorage = $tokenStorage;
        $this->translation = $translation;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CorrectOldPassword) {
            throw new UnexpectedTypeException($constraint, CorrectOldPassword::class);
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof ApplicationUser) {
            throw new UnexpectedTypeException($value, ApplicationUser::class);
        }

        if (!empty($value)) {
            $isValid = $this->passwordEncoder->isPasswordValid($user->getPassword(), $value, $user->getSalt());
            if (!$isValid) {
                $this->context->buildViolation($this->translation->trans($constraint->message, [], "validations"))
                    ->atPath('oldPassword')
                    ->addViolation();
            }
        }
    }

}