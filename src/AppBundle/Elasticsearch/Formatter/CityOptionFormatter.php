<?php


namespace AppBundle\Elasticsearch\Formatter;


class CityOptionFormatter
{
    public function formatCityOption(string $postalCode, string $label)
    {
        return "$label ($postalCode)";
    }

    public function extractPostalCodeFromOption(string $option): array
    {
        // find the part which starts by a parenthesis
        $fragments = substr($option, strpos($option, '('));
        // then remove the parenthesis
        $fragments = str_replace(['(',')'],'', $fragments);
        // multiple postalCodes might be contained in option
        return explode('/', $fragments);
    }
}
