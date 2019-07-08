<?php

namespace Wamcar\Vehicle\Enum;


use MyCLabs\Enum\Enum;

/**
 * @method static LeadCriteriaSelection LEAD_CRITERIA_NO_MATTER()
 * @method static LeadCriteriaSelection LEAD_CRITERIA_WITH()
 * @method static LeadCriteriaSelection LEAD_CRITERIA_WITHOUT()
 */
final class LeadCriteriaSelection extends Enum
{
    const LEAD_CRITERIA_NO_MATTER = 'leadcriteria.no_matter';
    const LEAD_CRITERIA_WITH = 'leadcriteria.with';
    const LEAD_CRITERIA_WITHOUT = 'leadcriteria.without';
}