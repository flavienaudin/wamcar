<?php

namespace AppBundle\Form\DataTransformer;

use AppBundle\Form\Type\SpecificField\YesNoType;
use Symfony\Component\Form\DataTransformerInterface;

class YesNoDataTransformer implements DataTransformerInterface
{
    /**
     * @param mixed $value
     * @return string
     */
    public function transform($value): string
    {
        return $value ? YesNoType::YES : YesNoType::NO;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function reverseTransform($value): bool
    {
        switch ($value) {
            case YesNoType::YES:
                return true;
            case YesNoType::NO:
            default:
                return false;
        }
    }
}
