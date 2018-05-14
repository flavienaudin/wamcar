<?php


namespace AppBundle\Form\DataTransformer;


use Symfony\Component\Form\DataTransformerInterface;

class AmountTransformer implements DataTransformerInterface
{
    /**
     * Transforms an float to a string (float).
     *
     * @param  float $value
     * @return string
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * Transforms a string (float) to an float.
     *
     * @param  string $value
     * @return float
     */
    public function reverseTransform($value)
    {
        $value = str_replace(',', '.', $value);
        return (float) preg_replace("/[^0-9,.]/", "", $value);
    }
}
