<?php

namespace AppBundle\Elasticsearch\Traits;

use AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder;
use AppBundle\Elasticsearch\Builder\IndexableSearchItemBuilder;
use AppBundle\Elasticsearch\Elastica\EntityIndexer;
use AppBundle\Elasticsearch\Elastica\ProVehicleEntityIndexer;
use Wamcar\Vehicle\ProVehicle;

trait ProVehicleIndexerTrait
{
    /** @var ProVehicleEntityIndexer */
    protected $proVehicleEntityIndexer;
    /** @var IndexableProVehicleBuilder */
    protected $indexableProVehicleBuilder;
    /** @var EntityIndexer */
    protected $searchItemEntityIndexer;
    /** @var IndexableSearchItemBuilder */
    protected $indexableSearchItemBuilder;

    /**
     * IndexUpdatedProVehicle constructor.
     * @param ProVehicleEntityIndexer $proVehicleEntityIndexer
     * @param IndexableProVehicleBuilder $indexableProVehicleBuilder
     * @param EntityIndexer $searchItemEntityIndexer
     * @param IndexableSearchItemBuilder $indexableSearchItemBuilder
     */
    public function __construct(ProVehicleEntityIndexer $proVehicleEntityIndexer,
                                IndexableProVehicleBuilder $indexableProVehicleBuilder,
                                EntityIndexer $searchItemEntityIndexer,
                                IndexableSearchItemBuilder $indexableSearchItemBuilder
    )
    {
        $this->proVehicleEntityIndexer = $proVehicleEntityIndexer;
        $this->indexableProVehicleBuilder = $indexableProVehicleBuilder;
        $this->searchItemEntityIndexer = $searchItemEntityIndexer;
        $this->indexableSearchItemBuilder = $indexableSearchItemBuilder;
    }

    protected function indexProVehicle(ProVehicle $proVehicle)
    {
        $this->proVehicleEntityIndexer->updateIndexable($this->indexableProVehicleBuilder->buildFromVehicle($proVehicle));
        $this->searchItemEntityIndexer->updateIndexable($this->indexableSearchItemBuilder->createSearchItemFromProVehicle($proVehicle));
    }
}
