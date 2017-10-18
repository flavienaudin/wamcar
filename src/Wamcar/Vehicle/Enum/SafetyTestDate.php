<?php

namespace Wamcar\Vehicle\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static SafetyTestState LESS_THAN_SIX_MONTH()
 * @method static SafetyTestState MORE_THAN_SIX_MONTH()
 * @method static SafetyTestState NOT_APPLICABLE()
 * @method static SafetyTestState UNKNOWN()
 */
final class SafetyTestDate extends Enum
{
    const LESS_THAN_SIX_MONTH = 'less_than_six_month';
    const MORE_THAN_SIX_MONTH = 'more_than_six_month';
    const NOT_APPLICABLE = 'not_applicable';
    const UNKNOWN = 'unknown';
}
