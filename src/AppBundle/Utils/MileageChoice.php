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
            '0 Km' => '0',
            '5 000 Km' => '5000',
            '7 500 Km' => '7500',
            '10 000 Km' => '10000',
            '12 500 Km' => '12500',
            '15 000 Km' => '15000',
            '20 000 Km' => '20000',
            '30 000 Km' => '30000',
            '40 000 Km' => '40000',
            '50 000 Km' => '50000',
            '60 000 Km' => '60000',
            '70 000 Km' => '70000',
            '80 000 Km' => '80000',
            '90 000 Km' => '90000',
            '100 000 Km' => '100000',
            '125 000 Km' => '125000',
            '150 000 Km' => '150000',
            '175 000 Km' => '175000',
            '200 000 Km' => '200000',
            '250 000 Km' => '250000'
        ];
    }
}
