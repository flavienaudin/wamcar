<?php

namespace AppBundle\Form\Validator\Constraints;


use AppBundle\Form\DTO\GarageDTO;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Wamcar\Garage\Garage;

class GarageAddressRequiredValidator extends ConstraintValidator
{
    /** @var TranslatorInterface */
    private $translation;

    public function __construct(TranslatorInterface $translation)
    {
        $this->translation = $translation;
    }

    public function validate($value, Constraint $constraint)
    {

        if (!$constraint instanceof GarageAddressRequired) {
            throw new UnexpectedTypeException($constraint, GarageAddressRequired::class);
        }
        if (!$value instanceof Garage && !$value instanceof GarageDTO) {
            throw new UnexpectedTypeException($value, Garage::class . '||' . GarageDTO::class);
        }
        if (empty($value->getAddress())) {
            $this->context->buildViolation($this->translation->trans($constraint->message, [], "validations"))
                ->addViolation();
        }
    }
}