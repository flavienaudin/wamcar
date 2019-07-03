<?php

namespace AppBundle\Doctrine\Type;


use Wamcar\User\Enum\LeadInitiatedBy;

final class LeadInitiatedByType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'lead_initiated_by';
    /** @var string */
    protected $enumClass = LeadInitiatedBy::class;
}