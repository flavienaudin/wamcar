<?php

namespace AppBundle\Doctrine\Type;

use Wamcar\Vehicle\Enum\Transmission;

final class VehicleTransmissionType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'vehicle_transmission';
    /** @var string */
    protected $enumClass = Transmission::class;
}
