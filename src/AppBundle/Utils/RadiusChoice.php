<?php

namespace AppBundle\Utils;


class RadiusChoice
{

    public static function getListRadius(): array
    {
        return [
            '10 km' => 10,
            '20 km' => 20,
            '30 km' => 30,
            '50 km' => 50,
            '100 km' => 100,
            '200 km' => 200,
            '300 km' => 300
        ];
    }
}