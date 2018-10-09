<?php

namespace AppBundle\Doctrine\Type;


use Wamcar\Garage\Enum\GarageRole;

class GarageRoleType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'garage_role';
    /** @var string */
    protected $enumClass = GarageRole::class;

}