<?php

namespace Wamcar\Vehicle\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static Transmission TRANSMISSION_MANUAL()
 * @method static Transmission TRANSMISSION_AUTOMATIC()
 */
final class Transmission extends Enum
{
    const TRANSMISSION_MANUAL = 'TRANSMISSION_MANUAL';
    const TRANSMISSION_AUTOMATIC = 'TRANSMISSION_AUTOMATIC';
}
