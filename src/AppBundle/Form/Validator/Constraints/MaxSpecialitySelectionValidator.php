<?php


namespace AppBundle\Form\Validator\Constraints;


use AppBundle\Form\DTO\ProUserProServiceSpecialityDTO;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MaxSpecialitySelectionValidator extends ConstraintValidator
{

    /** @var TranslatorInterface */
    private $translation;

    public function __construct(TranslatorInterface $translation)
    {
        $this->translation = $translation;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof MaxSpecialitySelection) {
            throw new UnexpectedTypeException($constraint, MaxSpecialitySelection::class);
        }
        if (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        $nbSelection = 0;
        /** @var ProUserProServiceSpecialityDTO $proUserProServiceSpecialityDTO */
        foreach ($value as $proUserProServiceSpecialityDTO) {
            if ($proUserProServiceSpecialityDTO->isSpeciality()) {
                $nbSelection++;
            }
        }

        if ($nbSelection > $constraint->max) {
            $this->context->buildViolation($this->translation->trans($constraint->message, ['%max%' => $constraint->max], "validations"))
                ->setParameter('%max%', $constraint->max)
                ->atPath('proUserProServicesForSpecialities')
                ->addViolation();
        }
    }
}