<?php


namespace AppBundle\Form\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

class SaleDeclaration extends Constraint
{
    public $message = 'constraint.sale_declaration.mandatory_transaction';
}