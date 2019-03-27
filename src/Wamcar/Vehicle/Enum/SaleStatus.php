<?php

namespace Wamcar\Vehicle\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static SaleStatus SOLD_WITH_WAMCAR()
 * @method static SaleStatus SOLD_WITOUT_WAMCAR()
 * @method static SaleStatus NO_LONGER_SOLD()
 * @method static SaleStatus OTHER()
 */
final class SaleStatus extends Enum
{
    const SOLD_WITH_WAMCAR= 'SOLD_WITH_WAMCAR';
    const SOLD_WITOUT_WAMCAR = 'SOLD_WITOUT_WAMCAR';
    const NO_LONGER_SOLD = 'NO_LONGER_SOLD';
    const OTHER = 'OTHER';
}
