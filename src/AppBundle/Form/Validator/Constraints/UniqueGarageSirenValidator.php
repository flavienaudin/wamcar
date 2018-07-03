<?php

namespace AppBundle\Form\Validator\Constraints;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\GarageDTO;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;

class UniqueGarageSirenValidator extends ConstraintValidator
{

    /** @var GarageRepository $garageRepository */
    private $garageRepository;
    /** @var TranslatorInterface */
    private $translation;
    /** @var SessionInterface */
    protected $session;
    /** @var UrlGeneratorInterface */
    protected $router;

    public function __construct(GarageRepository $garageRepository, TranslatorInterface $translation, SessionInterface $session, UrlGeneratorInterface $router)
    {
        $this->garageRepository = $garageRepository;
        $this->translation = $translation;
        $this->session = $session;
        $this->router = $router;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueGarageSiren) {
            throw new UnexpectedTypeException($constraint, UniqueGarageSiren::class);
        }

        if (!$value instanceof Garage && !$value instanceof GarageDTO) {
            throw new UnexpectedTypeException($value, Garage::class . '||' . GarageDTO::class);
        }

        if(!empty($value->getSiren())) {
            $garage = $this->garageRepository->findOneBy(['siren' => $value->getSiren()]);

            if ($garage != null && ($garage->getId() !== $value->getId())) {
                $this->context->buildViolation($this->translation->trans($constraint->message, ['%siren%' => $value->getSiren()], "validations"))
                    ->setParameter('%siren%', $value->getSiren())
                    ->atPath('siren')
                    ->addViolation();

                $this->session->getFlashBag()->add(
                    BaseController::FLASH_LEVEL_DANGER,
                    $this->translation->trans('flash.error.already_registered_siren_by_user', [
                        '%userFullName%' => $garage->getSeller()->getFullName(),
                        '%contactUrl%' => $this->router->generate('contact')
                    ])
                );
            }
        }
    }

}