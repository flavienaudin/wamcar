<?php

namespace Wamcar\Vehicle\Enum;


use MyCLabs\Enum\Enum;

/**
 * @method static NotificationFrequency IMMEDIATELY()
 * @method static NotificationFrequency ONCE_A_DAY()
 */
final class NotificationFrequency extends Enum
{
    const IMMEDIATELY = 'IMMEDIATELY';
    const ONCE_A_DAY = 'ONCE_A_DAY';
}