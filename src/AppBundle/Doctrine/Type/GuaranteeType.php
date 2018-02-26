<?php

namespace AppBundle\Doctrine\Type;

use Wamcar\Vehicle\Enum\Guarantee;

final class GuaranteeType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'vehicle_guarantee';
    /** @var string */
    protected $enumClass = Guarantee::class;
}
