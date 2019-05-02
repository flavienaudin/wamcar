<?php

namespace AppBundle\Doctrine\Type;


use Wamcar\User\Enum\LeadStatus;

final class LeadStatusType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'lead_status';
    /** @var string */
    protected $enumClass = LeadStatus::class;
}