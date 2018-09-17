<?php

namespace AppBundle\Elasticsearch\Traits;

use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder;
use AppBundle\Elasticsearch\Type\IndexableProUser;
use AppBundle\Elasticsearch\Type\IndexableProVehicle;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;
use Wamcar\Vehicle\ProVehicle;

trait GarageIndexerTrait
{
    /** @var ObjectIndexer */
    private $objectIndexer;
    /** @var IndexableProVehicleBuilder */
    private $indexableProVehicleBuilder;

    protected function indexUpdatedGarage(Garage $garage)
    {
        /** @var ProVehicle $garageMember */
        foreach ($garage->getProVehicles() as $proVehicle) {
            $indexableProVehicle = $this->indexableProVehicleBuilder->buildFromVehicle($proVehicle);

            if ($indexableProVehicle->shouldBeIndexed()) {
                $this->objectIndexer->index($indexableProVehicle, IndexableProVehicle::TYPE);
            } else {
                $this->objectIndexer->remove($indexableProVehicle, IndexableProVehicle::TYPE);
            }
        }

        /** @var GarageProUser $garageMember */
        foreach ($garage->getMembers() as $garageMember) {
            /** @var ProApplicationUser $proUser */
            $proUser = $garageMember->getProUser();
            $indexableProUser = IndexableProUser::createFromProApplicationUser($proUser);
            if ($indexableProUser->shouldBeIndexed()) {
                $this->objectIndexer->index($indexableProUser, IndexableProUser::TYPE);
            } else {
                $this->objectIndexer->remove($indexableProUser, IndexableProUser::TYPE);
            }
        }
    }
}
