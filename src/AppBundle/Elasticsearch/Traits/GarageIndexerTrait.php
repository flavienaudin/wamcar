<?php

namespace AppBundle\Elasticsearch\Traits;

use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder;
use AppBundle\Elasticsearch\Builder\IndexableSearchItemBuilder;
use AppBundle\Elasticsearch\Elastica\EntityIndexer;
use AppBundle\Elasticsearch\Elastica\ProUserEntityIndexer;
use AppBundle\Elasticsearch\Elastica\ProVehicleEntityIndexer;
use AppBundle\Elasticsearch\Type\IndexableProUser;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;
use Wamcar\Vehicle\ProVehicle;

trait GarageIndexerTrait
{
    /** @var ProUserEntityIndexer */
    private $proUserEntityIndexer;
    /** @var ProVehicleEntityIndexer */
    private $proVehicleEntityIndexer;
    /** @var IndexableProVehicleBuilder */
    private $indexableProVehicleBuilder;
    /** @var EntityIndexer */
    private $searchItemEntityIndexer;
    /** @var IndexableSearchItemBuilder */
    private $indexableSearchIdemBuilder;

    /**
     * IndexCreatedVehicle constructor.
     * @param ProUserEntityIndexer $proUserEntityIndexer
     * @param ProVehicleEntityIndexer $proVehicleEntityIndexer
     * @param EntityIndexer $searchItemEntityIndexer
     * @param IndexableProVehicleBuilder $indexableProVehicleBuilder
     * @param IndexableSearchItemBuilder $indexableSearchItemBuilder
     */
    public function __construct(ProUserEntityIndexer $proUserEntityIndexer,
                                ProVehicleEntityIndexer $proVehicleEntityIndexer,
                                IndexableProVehicleBuilder $indexableProVehicleBuilder,
                                EntityIndexer $searchItemEntityIndexer,
                                IndexableSearchItemBuilder $indexableSearchItemBuilder)
    {
        $this->proUserEntityIndexer = $proUserEntityIndexer;
        $this->proVehicleEntityIndexer = $proVehicleEntityIndexer;
        $this->indexableProVehicleBuilder = $indexableProVehicleBuilder;
        $this->searchItemEntityIndexer = $searchItemEntityIndexer;
        $this->indexableSearchItemBuilder = $indexableSearchItemBuilder;
    }


    protected function indexUpdatedGarage(Garage $garage)
    {
        /* B2B model without vehicle
        $proVehicleDocuments = [];
        /** @var ProVehicle $proVehicle *
        foreach ($garage->getProVehicles() as $proVehicle) {
            $this->proVehicleEntityIndexer->updateIndexable($this->indexableProVehicleBuilder->buildFromVehicle($proVehicle));
            $indexableProVehicle = $this->indexableSearchItemBuilder->createSearchItemFromProVehicle($proVehicle);
            if ($indexableProVehicle->shouldBeIndexed()) {
                $proVehicleDocuments[] = $this->searchItemEntityIndexer->buildDocument($indexableProVehicle);
            }
        }
        if (count($proVehicleDocuments) > 0) {
            $this->searchItemEntityIndexer->indexAllDocuments($proVehicleDocuments, true);
        }*/

        /** @var GarageProUser $garageMember */
        foreach ($garage->getMembers() as $garageMember) {
            /** @var ProApplicationUser $proUser */
            $proUser = $garageMember->getProUser();
            $this->proUserEntityIndexer->updateIndexable(IndexableProUser::createFromProApplicationUser($proUser));
        }

    }
}
