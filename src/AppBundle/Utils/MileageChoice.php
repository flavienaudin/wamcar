<?php


namespace AppBundle\Utils;


class MileageChoice
{
    /**
     * Return a list of mileage
     *
     * @return array
     */
    public static function getMileageMax(): array
    {
        return [
            '50 000 Km' => '50000',
            '100 000 Km' => '100000',
            '150 000 Km' => '150000',
            '200 000 Km' => '200000'
        ];
    }
}
