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
            return $value ? VehicleStatutType::USED : VehicleStatutType::NEW;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function reverseTransform($value): ?bool
    {
        switch ($value) {
            case VehicleStatutType::NEW:
                return false;
            case VehicleStatutType::USED:
                return true;
            default:
                return null;
        }
    }
}
