<?php

namespace AppBundle\Form\Validator\Constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Wamcar\Garage\GarageRepository;

class UniqueGarageSirenValidator extends ConstraintValidator
{

    /** @var GarageRepository $garageRepository */
    private $garageRepository;

    public function __construct(GarageRepository $garageRepository)
    {
        $this->garageRepository = $garageRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        $garage = $this->garageRepository->findOneBy(['siren' => $value]);

        if ($garage != null) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ siren }}', $value)
                ->addViolation();
        }
    }

}