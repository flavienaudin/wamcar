<?php

namespace AppBundle\Form\DataTransformer;

use MyCLabs\Enum\Enum;
use Symfony\Component\Form\DataTransformerInterface;

class EnumDataTransformer implements DataTransformerInterface
{
    /** @var string */
    private $className;

    /**
     * EnumDataTransformer constructor.
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * @param mixed $value
     * @return null|string
     * @throws \LogicException when provided data is not an Enum
     */
    public function transform($value): ?string
    {
        if ($value === null) {
            return null;
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
     * @return Enum
     */
    public function reverseTransform($value): ?Enum
    {
        return new $this->className($value);
    }
}
