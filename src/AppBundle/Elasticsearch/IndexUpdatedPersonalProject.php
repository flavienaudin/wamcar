<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Elasticsearch\Builder\IndexablePersonalProjectBuilder;
use AppBundle\Elasticsearch\Traits\PersonalProjectIndexerTrait;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\User\Event\PersonalProjectUpdated;
use Wamcar\User\Event\ProjectEvent;
use Wamcar\User\Event\ProjectEventHandler;

class IndexUpdatedPersonalProject implements ProjectEventHandler
{
    use PersonalProjectIndexerTrait;

    /**
     * IndexUpdatedPersonalProject constructor.
     * @param ObjectIndexer $objectIndexer
     * @param IndexablePersonalProjectBuilder $indexablePersonalProjectBuilder
     */
    public function __construct(ObjectIndexer $objectIndexer, IndexablePersonalProjectBuilder $indexablePersonalProjectBuilder)
    {
        $this->objectIndexer = $objectIndexer;
        $this->indexablePersonalProjectBuilder = $indexablePersonalProjectBuilder;
    }

    /**
     * @param ProjectEvent $event
     */
    public function notify(ProjectEvent $event)
    {
        if (!$event instanceof PersonalProjectUpdated) {
            throw new \InvalidArgumentException("IndexUpdatedPersonalProject can only be notified of 'PersonalProjectUpdated' events");
        }
        $personalProject = $event->getProject();

        $this->indexPersonalProject($personalProject);
    }

}
