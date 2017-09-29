<?php

namespace Wamcar\Vehicle\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static Fuel GASOLINE()
 * @method static Fuel DIESEL()
 * @method static Fuel LPG()
 * @method static Fuel HYBRID()
 * @method static Fuel ELECTRIC()
 */
final class Fuel extends Enum
{
    const GASOLINE = 'gasoline';
    const DIESEL = 'diesel';
    const LPG = 'lpg';
    const HYBRID = 'hybrid';
    const ELECTRIC = 'electric';
}
