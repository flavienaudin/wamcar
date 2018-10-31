<?php

namespace AppBundle\Command;


use AppBundle\Elasticsearch\Type\IndexableVehicleInfo;

class ClearVehicleInfoCommand extends AbstractClearCommand
{

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('wamcar:clear:vehicle_info',
            'Clear all vehicle infos (models) from the index',
            IndexableVehicleInfo::TYPE,
            'ktypNumber'
        );
    }

}