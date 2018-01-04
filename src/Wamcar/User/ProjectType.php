<?php

namespace Wamcar\User;

use MyCLabs\Enum\Enum;

/**
 * @method static ProjectType UNIQUE()
 * @method static Title FLOTTE()
 */
class ProjectType extends Enum
{
    const UNIQUE = 'Véhicule unique';
    const FLOTTE = 'Une flotte';
}
