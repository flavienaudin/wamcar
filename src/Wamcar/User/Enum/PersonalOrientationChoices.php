<?php

namespace Wamcar\User\Enum;


use MyCLabs\Enum\Enum;

/**
 * Class PersonalOrientationChoices
 * @package Wamcar\User\Enum
 *
 * @method static PersonalOrientationChoices PERSONAL_ORIENTATION_SELL()
 * @method static PersonalOrientationChoices PERSONAL_ORIENTATION_BUY()
 * @method static PersonalOrientationChoices PERSONAL_ORIENTATION_BOTH()
 */
class PersonalOrientationChoices extends Enum
{
    const PERSONAL_ORIENTATION_SELL = 'PERSONAL_ORIENTATION_SELL';
    const PERSONAL_ORIENTATION_BUY = 'PERSONAL_ORIENTATION_BUY';
    const PERSONAL_ORIENTATION_BOTH = 'PERSONAL_ORIENTATION_BOTH';
}