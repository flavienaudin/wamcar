<?php

namespace AppBundle\Form\DataTransformer;

use MyCLabs\Enum\Enum;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EnumDataTransformer implements DataTransformerInterface
{
    /** @var string */
    private $className;

    /**
     * EnumToChoiceList constructor.
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function transform($value): ?string
    {
        if($value === null) {
            return null;
        }
dump($value);
        $value = is_string($value) ? new $this->className($value) : $value;
dump($value);
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
//        if (!is_subclass_of($enumClassName, Enum::class)) {
//            throw new LogicException(sprintf('"%s" should be an instance of "%s" to be transformed into a choice list', $enumClassName, Enum::class));
//        }
//
//        $enumValues = call_user_func([$enumClassName, 'toArray']);
//
//        $choices = [];
//        foreach ($enumValues as $value) {
//            $choices[$value] = $value;
//        }
//
//        return $choices;
    }

    /**
     * @param mixed $value
     * @return Enum
     */
    public function reverseTransform($value): ?Enum
    {
        return new $this->className($value);
    }

    //    public static function transform(string $enumClassName): array
    //    {
    //        if (!is_subclass_of($enumClassName, Enum::class)) {
    //            throw new LogicException(sprintf('"%s" should be an instance of "%s" to be transformed into a choice list', $enumClassName, Enum::class));
    //        }
    //
    //        $enumValues = call_user_func([$enumClassName, 'toArray']);
    //
    //        $choices = [];
    //        foreach ($enumValues as $value) {
    //            $choices[$value] = $value;
    //        }
    //
    //        return $choices;
    //    }
}
