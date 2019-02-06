<?php

namespace AppBundle\Elasticsearch\Traits;

use AppBundle\Elasticsearch\Builder\IndexableSearchItemBuilder;
use AppBundle\Elasticsearch\Elastica\EntityIndexer;
use AppBundle\Elasticsearch\Type\IndexablePersonalProject;
use Wamcar\User\PersonalUser;
use Wamcar\User\Project;

trait PersonalProjectIndexerTrait
{
    /** @var EntityIndexer */
    private $personalProjectEntityIndexer;
    /** @var EntityIndexer */
    private $searchItemEntityIndexer;
    /** @var IndexableSearchItemBuilder */
    private $indexableSearchItemBuilder;

    /**
     * IndexUpdatedPersonalProject constructor.
     * @param EntityIndexer $personalProjectEntityIndexer
     * @param EntityIndexer $searchItemEntityIndexer
     * @param IndexableSearchItemBuilder $indexableSearchItemBuilder
     */
    public function __construct(EntityIndexer $personalProjectEntityIndexer,
                                EntityIndexer $searchItemEntityIndexer,
                                IndexableSearchItemBuilder $indexableSearchItemBuilder)
    {
        $this->personalProjectEntityIndexer = $personalProjectEntityIndexer;
        $this->searchItemEntityIndexer = $searchItemEntityIndexer;
        $this->indexableSearchItemBuilder = $indexableSearchItemBuilder;

    }

    protected function indexPersonalProject(Project $project)
    {
        $this->personalProjectEntityIndexer->updateIndexable(IndexablePersonalProject::createFromPersonalProject($project));
    }

    protected function indexPersonalUserSearchItems(PersonalUser $personalUser)
    {
        $searchItemByOperation = $this->indexableSearchItemBuilder->createSearchItemsFromPersonalUser($personalUser);
        if (count($searchItemByOperation['toIndex']) > 0) {
            $this->searchItemEntityIndexer->indexAllDocuments($searchItemByOperation['toIndex'], true);
        }
        if (count($searchItemByOperation['toDelete']) > 0) {
            $this->searchItemEntityIndexer->deleteByIds($searchItemByOperation['toDelete']);
        }
    }

}
