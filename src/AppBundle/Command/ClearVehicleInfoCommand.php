<?php

namespace AppBundle\Command;


use AppBundle\Elasticsearch\Type\IndexableVehicleInfo;

class ClearVehicleInfoCommand extends AbstractClearCommand
{
    public function __construct()
    {
        parent::__construct('wamcar:clear:vehicle_info',
            'Clear all vehicle infos (models) from its index',
            IndexableVehicleInfo::TYPE
        );
    }

}