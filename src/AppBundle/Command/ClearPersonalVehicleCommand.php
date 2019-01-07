<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;

class ClearPersonalVehicleCommand extends AbstractClearCommand
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('wamcar:clear:personal_vehicle',
            'Clear all personal vehicles from the index',
            IndexablePersonalVehicle::TYPE);
    }
}
