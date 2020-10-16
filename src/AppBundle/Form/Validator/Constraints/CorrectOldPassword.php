<?php

namespace AppBundle\Form\Validator\Constraints;


use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class CorrectOldPassword extends Constraint
{
    public $message = 'constraint.user.incorrect_old_password';
}