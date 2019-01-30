<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexableProVehicle;

class ClearProVehicleCommand extends AbstractClearCommand
{
    public function __construct()
    {
        parent::__construct('wamcar:clear:pro_vehicle',
            'Clear all pro vehicles from its index',
            IndexableProVehicle::TYPE);
    }
}
