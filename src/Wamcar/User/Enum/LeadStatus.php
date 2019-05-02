<?php

namespace Wamcar\User\Enum;


use MyCLabs\Enum\Enum;

/**
 * Class LeadStatus
 * @method static LeadStatus TO_QUALIFY()
 * @method static LeadStatus CONTACTED()
 * @method static LeadStatus MET()
 * @method static LeadStatus QUOTE()
 * @method static LeadStatus NEGOCIATION()
 * @method static LeadStatus WON()
 * @method static LeadStatus LOST()
 */
class LeadStatus extends Enum
{
    const TO_QUALIFY = 'leadStatus.to_qualify';
    const CONTACTED = 'leadStatus.contacted';
    const MET = 'leadStatus.met';
    const QUOTE = 'leadStatus.quote';
    const NEGOCIATION = 'leadStatus.negociation';
    const WON = 'leadStatus.won';
    const LOST = 'leadStatus.lost';
}