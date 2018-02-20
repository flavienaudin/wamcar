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
        $dotPos = strrpos($value, '.');
        $commaPos = strrpos($value, ',');
        $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
            ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

        if (!$sep) {
            return floatval(preg_replace("/[^0-9]/", "", $value));
        }

        return floatval(
            preg_replace("/[^0-9]/", "", substr($value, 0, $sep)) . '.' .
            preg_replace("/[^0-9]/", "", substr($value, $sep+1, strlen($value)))
        );
    }
}
