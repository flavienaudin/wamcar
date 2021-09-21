<?php

namespace AppBundle\Elasticsearch\Traits;

use AppBundle\Elasticsearch\Builder\IndexablePersonalVehicleBuilder;
use AppBundle\Elasticsearch\Builder\IndexableSearchItemBuilder;
use AppBundle\Elasticsearch\Elastica\EntityIndexer;
use AppBundle\Elasticsearch\Elastica\PersonalVehicleEntityIndexer;
use Wamcar\User\PersonalUser;
use Wamcar\Vehicle\Event\PersonalVehicleCreated;
use Wamcar\Vehicle\Event\PersonalVehicleRemoved;
use Wamcar\Vehicle\Event\VehicleEvent;
use Wamcar\Vehicle\PersonalVehicle;

trait PersonalVehicleIndexerTrait
{
    /** @var PersonalVehicleEntityIndexer */
    private $personalVehicleEntityIndexer;
    /** @var IndexablePersonalVehicleBuilder */
    private $indexablePersonalVehicleBuilder;
    /** @var EntityIndexer */
    private $searchItemEntityIndexer;
    /** @var IndexableSearchItemBuilder */
    private $indexableSearchItemBuilder;

    /**
     * IndexUpdatedPersonalVehicle constructor.
     * @param PersonalVehicleEntityIndexer $personalVehicleEntityIndexer
     * @param EntityIndexer $searchItemEntityIndexer
     * @param IndexablePersonalVehicleBuilder $indexablePersonalVehicleBuilder
     * @param IndexableSearchItemBuilder $indexableSearchItemBuilder
     */
    public function __construct(PersonalVehicleEntityIndexer $personalVehicleEntityIndexer,
                                EntityIndexer $searchItemEntityIndexer,
                                IndexablePersonalVehicleBuilder $indexablePersonalVehicleBuilder,
                                IndexableSearchItemBuilder $indexableSearchItemBuilder)
    {
        $this->personalVehicleEntityIndexer = $personalVehicleEntityIndexer;
        $this->searchItemEntityIndexer = $searchItemEntityIndexer;
        $this->indexablePersonalVehicleBuilder = $indexablePersonalVehicleBuilder;
        $this->indexableSearchItemBuilder = $indexableSearchItemBuilder;
    }

    protected function indexPersonalVehicle(PersonalVehicle $personalVehicle)
    {
        // B2B model no vehicle : $this->personalVehicleEntityIndexer->updateIndexable($this->indexablePersonalVehicleBuilder->buildFromVehicle($personalVehicle));
    }

    protected function indexPersonalUserSearchItems(PersonalUser $personalUser, VehicleEvent $event)
    {
        if ($event instanceof PersonalVehicleCreated) {
            if ($personalUser->getProject() != null) {
                // Deletion of the SearchItem of the Project only. Indexed with the created vehicle as SearchItem
                $this->searchItemEntityIndexer->deleteByIds([$personalUser->getProject()->getId()]);
            }
        } elseif ($event instanceof PersonalVehicleRemoved) {
            // Deletion of the Personal Vehicle SearchItem
            $this->searchItemEntityIndexer->deleteByIds([$event->getVehicle()->getId()]);
        }

        // Index all documents
        $searchItemsByOperation = $this->indexableSearchItemBuilder->createSearchItemsFromPersonalUser($personalUser);
        if (count($searchItemsByOperation['toIndex']) > 0) {
            $this->searchItemEntityIndexer->indexAllDocuments($searchItemsByOperation['toIndex'], true);
        }
        if (count($searchItemsByOperation['toDelete']) > 0) {
            $this->searchItemEntityIndexer->deleteByIds($searchItemsByOperation['toDelete']);
        }
    }
}
