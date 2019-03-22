<?php

namespace AppBundle\Form\Validator\Constraints;


use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
class GarageAddressRequired extends Constraint
{
    public $message = 'constraint.garage.address.required';
}