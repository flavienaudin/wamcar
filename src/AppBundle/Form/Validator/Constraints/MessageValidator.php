<?php

namespace AppBundle\Form\Validator\Constraints;


use AppBundle\Form\DTO\MessageDTO;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MessageValidator extends ConstraintValidator
{

    /** @var TranslatorInterface */
    private $translation;

    public function __construct(TranslatorInterface $translation)
    {
        $this->translation = $translation;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Message) {
            throw new UnexpectedTypeException($constraint, Message::class);
        }

        if (!$value instanceof MessageDTO) {
            throw new UnexpectedTypeException($value, MessageDTO::class);
        }

        if ($value->vehicle == null && $value->content == null && count($value->attachments) == 0) {
            $this->context->buildViolation($this->translation->trans($constraint->message, [], "validations"))
                ->addViolation();
        }
    }

}
