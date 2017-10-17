<?php

namespace AppBundle\Doctrine\Type;

use Wamcar\Vehicle\Enum\SafetyTestState;

final class VehicleSafetyTestStateType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'vehicle_safety_test_state';
    /** @var string */
    protected $enumClass = SafetyTestState::class;
}
