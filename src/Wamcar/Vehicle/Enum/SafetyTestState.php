<?php

namespace Wamcar\Vehicle\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static SafetyTestState LESS_THAN_SIX_MONTH()
 * @method static SafetyTestState MORE_THAN_SIX_MONTH()
 * @method static SafetyTestState OK()
 * @method static SafetyTestState NOT_OK()
 * @method static SafetyTestState NOT_APPLICABLE()
 */
final class SafetyTestState extends Enum
{
    const LESS_THAN_SIX_MONTH = 'less_than_six_month';
    const MORE_THAN_SIX_MONTH = 'more_than_six_month';
    const OK = 'ok';
    const NOT_OK = 'not_ok';
    const NOT_APPLICABLE = 'not_applicable';
}


