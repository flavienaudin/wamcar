<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexableProVehicle;

class ClearProVehicleCommand extends AbstractClearCommand
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('wamcar:clear:pro_vehicle',
            'Clear all pro vehicles from the index',
            IndexableProVehicle::TYPE);
    }
}
