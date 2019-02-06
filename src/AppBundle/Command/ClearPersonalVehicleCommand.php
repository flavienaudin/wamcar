<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;

class ClearPersonalVehicleCommand extends AbstractClearCommand
{
    public function __construct()
    {
        parent::__construct('wamcar:clear:personal_vehicle',
            'Clear all personal vehicles from its index',
            IndexablePersonalVehicle::TYPE);
    }
}
