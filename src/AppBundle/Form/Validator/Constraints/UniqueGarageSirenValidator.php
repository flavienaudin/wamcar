<?php

namespace AppBundle\Form\Validator\Constraints;


use AppBundle\Form\DTO\GarageDTO;
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

    public function __construct(GarageRepository $garageRepository, TranslatorInterface $translation)
    {
        $this->garageRepository = $garageRepository;
        $this->translation = $translation;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueGarageSiren) {
            throw new UnexpectedTypeException($constraint, UniqueGarageSiren::class);
        }

        if (!$value instanceof Garage && !$value instanceof GarageDTO) {
            throw new UnexpectedTypeException($value, Garage::class . '||' . GarageDTO::class);
        }

        $garage = $this->garageRepository->findOneBy(['siren' => $value->getSiren()]);

        if ($garage != null && ($garage->getId() !== $value->getId())) {
            $this->context->buildViolation($this->translation->trans($constraint->message, ['%siren%' => $value->getSiren()], "validations"))
                ->setParameter('%siren%', $value->getSiren())
                ->atPath('siren')
                ->addViolation();
        }
    }

}