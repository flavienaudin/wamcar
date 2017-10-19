<?php

namespace AppBundle\Doctrine\Type;

use Wamcar\Vehicle\Enum\MaintenanceState;

final class VehicleMaintenanceStateType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'vehicle_maintenance_state';
    /** @var string */
    protected $enumClass = MaintenanceState::class;
}
