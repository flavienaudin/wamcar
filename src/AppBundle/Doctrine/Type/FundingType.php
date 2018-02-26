<?php

namespace AppBundle\Doctrine\Type;

use Wamcar\Vehicle\Enum\Funding;

final class FundingType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'vehicle_funding';
    /** @var string */
    protected $enumClass = Funding::class;
}
