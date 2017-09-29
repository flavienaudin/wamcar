<?php

namespace Wamcar\Vehicle\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static Fuel MANUAL()
 * @method static Fuel AUTOMATIC()
 */
final class Transmission extends Enum
{
    const MANUAL = 'manual';
    const AUTOMATIC = 'auto';
}
