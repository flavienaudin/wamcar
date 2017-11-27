<?php

namespace AppBundle\Elasticsearch\Traits;

use AppBundle\Elasticsearch\Builder\IndexablePersonalVehicleBuilder;
use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\Vehicle\PersonalVehicle;

trait PersonalVehicleIndexerTrait
{
    /** @var ObjectIndexer */
    private $objectIndexer;
    /** @var IndexablePersonalVehicleBuilder */
    private $indexablePersonalVehicleBuilder;

    protected function indexPersonalVehicle(PersonalVehicle $personalVehicle)
    {
        $indexablePersonalVehicle = $this->indexablePersonalVehicleBuilder->buildFromVehicle($personalVehicle);
        if ($indexablePersonalVehicle->shouldBeIndexed()) {
            $this->objectIndexer->index($indexablePersonalVehicle, IndexablePersonalVehicle::TYPE);
        } else {
            $this->objectIndexer->remove($indexablePersonalVehicle, IndexablePersonalVehicle::TYPE);
        }
    }
}
