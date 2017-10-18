<?php

namespace Wamcar\Vehicle\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static SafetyTestState OK()
 * @method static SafetyTestState NOT_OK()
 * @method static SafetyTestState UNKNOWN()
 */
final class SafetyTestState extends Enum
{
    const OK = 'ok';
    const NOT_OK = 'not_ok';
    const UNKNOWN = 'unknown';
}


