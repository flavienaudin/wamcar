<?php

namespace AppBundle\Elasticsearch\Traits;

use AppBundle\Elasticsearch\Builder\IndexablePersonalVehicleBuilder;
use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\User\PersonalUser;

trait PersonalUserIndexerTrait
{
    /** @var ObjectIndexer */
    private $objectIndexer;
    /** @var IndexablePersonalVehicleBuilder */
    private $indexablePersonalVehicleBuilder;

    protected function indexUpdatedPersonalUser(PersonalUser $personalUser)
    {
        foreach ($personalUser->getVehicles() as $vehicle) {
            $indexablePersonalVehicle = $this->indexablePersonalVehicleBuilder->buildFromVehicle($vehicle);

            if ($indexablePersonalVehicle->shouldBeIndexed()) {
                $this->objectIndexer->index($indexablePersonalVehicle, IndexablePersonalVehicle::TYPE);
            } else {
                $this->objectIndexer->remove($indexablePersonalVehicle, IndexablePersonalVehicle::TYPE);
            }
        }
    }
}
