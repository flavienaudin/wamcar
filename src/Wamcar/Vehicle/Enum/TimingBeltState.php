<?php

namespace Wamcar\Vehicle\Enum;


use MyCLabs\Enum\Enum;

/**
 * Class TimingBeltState
 * @package Wamcar\Vehicle\Enum
 *
 * @method static TimingBeltState TIMING_BELT_OK()
 * @method static TimingBeltState TO_CHANGE()
 * @method static TimingBeltState NO_TIMING_BELT()
 * @method static TimingBeltState UNKNOWN()
 */
class TimingBeltState extends Enum
{
    const TIMING_BELT_OK = 'ok';
    const TO_CHANGE = 'to_change';
    const NO_TIMING_BELT = 'no_timing_belt';
    const UNKNOWN = 'unknown';
}