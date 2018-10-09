<?php

namespace Wamcar\Garage\Enum;


use MyCLabs\Enum\Enum;

/**
 * Class GarageRole
 * @package Wamcar\Garage\Enum
 *
 * @method static GarageRole GARAGE_ADMINISTRATOR()
 * @method static GarageRole GARAGE_MEMBER()
 */
class GarageRole extends Enum
{
    const GARAGE_ADMINISTRATOR = "GARAGE.ADMINISTRATOR";
    const GARAGE_MEMBER = "GARAGE.MEMBER";
}