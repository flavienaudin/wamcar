<?php

namespace AppBundle\Form\DataTransformer;

use MyCLabs\Enum\Enum;
use Symfony\Component\Form\DataTransformerInterface;

class EnumDataTransformer implements DataTransformerInterface
{
    /** @var string */
    private $className;
    /** @var Enum */
    private $default;

    /**
     * EnumDataTransformer constructor.
     * @param string $className
     */
    public function __construct($className, Enum $default = null)
    {
        $this->className = $className;
        $this->default = $default;
    }

    /**
     * @param mixed $value
     * @return null|mixed
     * @throws \LogicException when provided data is not an Enum
     */
    public function transform($value)
    {
        if ($value === null) {
            return null;
        }

        if(is_array($value)){
            $transformedValues = [];
            foreach($value as $val) {
                $transformedValues[] = $this->transform($val);
            }
            return $transformedValues;
        }
        $value = is_string($value) ? new $this->className($value) : $value;

        if (!$value instanceof Enum) {
            throw new \LogicException(
                sprintf(
                    '"%s::transform" expect source data to be of type "%s"',
                    self::class,
                    Enum::class
                )
            );
        }

        return $value->getValue();
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function reverseTransform($value)
    {
        if(empty($value)) {
            return $this->default;
        }

        if(is_array($value)){
            $reversedValues = [];
            foreach($value as $val) {
                $reversedValues[] = $this->reverseTransform($val);
            }
            return $reversedValues;
        }

        return new $this->className($value);
    }
}
