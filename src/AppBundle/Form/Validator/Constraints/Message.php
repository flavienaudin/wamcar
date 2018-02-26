<?php

namespace AppBundle\Form\Validator\Constraints;


use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class Message extends Constraint
{
    public $message = 'constraint.message.not_empty';
}
