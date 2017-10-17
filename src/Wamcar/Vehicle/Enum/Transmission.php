<?php

namespace Wamcar\Vehicle\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static Transmission MANUAL()
 * @method static Transmission AUTOMATIC()
 */
final class Transmission extends Enum
{
    const MANUAL = 'manual';
    const AUTOMATIC = 'auto';
}
