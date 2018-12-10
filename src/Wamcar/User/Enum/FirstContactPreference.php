<?php

namespace Wamcar\User\Enum;


use MyCLabs\Enum\Enum;

/**
 * Class FirstContactPreference
 * @package Wamcar\User\Enum
 *
 * @method static FirstContactPreference I_WILL_BEGIN()
 * @method static FirstContactPreference I_M_WAITING ()
 */
class FirstContactPreference extends Enum
{
    const I_WILL_BEGIN = 'I_WILL_BEGIN';
    const I_M_WAITING = 'I_M_WAITING';
}