<?php

namespace AppBundle\Doctrine\Type;


use Wamcar\Vehicle\Enum\SaleStatus;

class VehicleSaleStatusType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'vehicle_sale_status';
    /** @var string */
    protected $enumClass = SaleStatus::class;
}