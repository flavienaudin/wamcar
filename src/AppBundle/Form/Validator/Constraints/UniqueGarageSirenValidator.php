<?php

namespace AppBundle\Form\Validator\Constraints;


use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
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
        $garage = $this->garageRepository->findOneBy(['siren' => $value]);

        if ($garage != null) {
            $this->context->buildViolation($this->translation->trans($constraint->message, ['%siren%' => $value], "validations" ))
                ->setParameter('%siren%',  $value)
                ->addViolation();
        }
    }

}