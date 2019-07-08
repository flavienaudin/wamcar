<?php


namespace AppBundle\Doctrine\Type;


use Wamcar\Vehicle\Enum\LeadCriteriaSelection;

final class LeadCriteriaSelectionType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'leadcriteria_selection';
    /** @var string */
    protected $enumClass = LeadCriteriaSelection::class;
}