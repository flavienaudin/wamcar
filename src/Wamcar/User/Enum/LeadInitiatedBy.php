<?php

namespace Wamcar\User\Enum;


use MyCLabs\Enum\Enum;

/**
 * Class LeadInitiatedBy
 * @method static LeadInitiatedBy PRO_USER()
 * @method static LeadInitiatedBy LEAD()
 */
class LeadInitiatedBy extends Enum
{
    const PRO_USER= 'leadInitiatedBy.pro_user';
    const LEAD = 'leadInitiatedBy.lead';
}