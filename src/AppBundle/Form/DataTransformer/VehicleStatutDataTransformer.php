<?php

namespace AppBundle\Form\DataTransformer;

use AppBundle\Form\Type\SpecificField\VehicleStatutType;
use Symfony\Component\Form\DataTransformerInterface;

class VehicleStatutDataTransformer implements DataTransformerInterface
{
    /**
     * @param mixed $value
     * @return string|null
     */
    public function transform($value): ?string
    {
        if ($value === null)
            return null;
        else
            return $value ? VehicleStatutType::NEW : VehicleStatutType::SECOND_HAND;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function reverseTransform($value): ?bool
    {
        switch ($value) {
            case VehicleStatutType::NEW:
                return true;
            case VehicleStatutType::SECOND_HAND:
                return false;
            default:
                return null;
        }
    }
}