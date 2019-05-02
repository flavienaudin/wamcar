<?php


namespace AppBundle\Form\Validator\Constraints;


use AppBundle\Form\DTO\SaleDeclarationDTO;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SaleDeclarationValidator extends ConstraintValidator
{
    /** @var TranslatorInterface */
    private $translation;

    public function __construct(TranslatorInterface $translation)
    {
        $this->translation = $translation;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof SaleDeclaration) {
            throw new UnexpectedTypeException($constraint, SaleDeclaration::class);
        }

        if (!$value instanceof SaleDeclarationDTO) {
            throw new UnexpectedTypeException($value, SaleDeclarationDTO::class);
        }

        if (empty($value->getTransactionSaleAmount()) && empty($value->getTransactionPartExchangeAmount())) {
            $this->context->buildViolation($this->translation->trans($constraint->message, [], "validations"))
                ->atPath('transactionSaleAmount')
                ->addViolation();
        }
    }
}