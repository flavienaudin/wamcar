<?php

namespace AppBundle\Doctrine\Type;

use Wamcar\Vehicle\Enum\SafetyTestDate;

final class VehicleSafetyTestDateType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'vehicle_safety_test_date';
    /** @var string */
    protected $enumClass = SafetyTestDate::class;
}
