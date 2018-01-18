<?php

namespace AppBundle\Form\Validator\Constraints;


use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class UniqueGarageSiren extends Constraint
{
    public $message = 'constraint.already_registered_garage_siren';
}