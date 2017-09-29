<?php

namespace Wamcar\Vehicle\Enum;

use MyCLabs\Enum\Enum;

/**
 * @method static MaintenanceState UP_TO_DATE_WITH_INVOICES()
 * @method static MaintenanceState UP_TO_DATE()
 * @method static MaintenanceState MISSING()
 * @method static MaintenanceState UNKNOWN()
 */
final class MaintenanceState extends Enum
{
    const UP_TO_DATE_WITH_INVOICES = 'up_to_date_with_invoices';
    const UP_TO_DATE = 'up_to_date';
    const MISSING = 'missing';
    const UNKNOWN = 'unknown';
}


