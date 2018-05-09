<?php

namespace AppBundle\Doctrine\Type;


use Wamcar\Vehicle\Enum\TimingBeltState;

class VehicleTimingBeltStateType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'vehicle_timing_belt_state';
    /** @var string */
    protected $enumClass = TimingBeltState::class;
}