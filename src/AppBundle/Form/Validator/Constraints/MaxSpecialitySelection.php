<?php


namespace AppBundle\Form\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

class MaxSpecialitySelection extends Constraint
{
    public $message = 'constraint.max_speciality_selection.max_selection';
    public $max = 3;
}