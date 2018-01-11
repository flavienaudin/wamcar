<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Elasticsearch\Builder\IndexablePersonalVehicleBuilder;
use AppBundle\Elasticsearch\Traits\PersonalUserIndexerTrait;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\User\Event\PersonalUserUpdated;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;

class IndexUpdatedPersonalUser implements UserEventHandler
{
    use PersonalUserIndexerTrait;

    /**
     * IndexCreatedVehicle constructor.
     * @param ObjectIndexer $objectIndexer
     * @param IndexablePersonalVehicleBuilder $indexablePersonalVehicleBuilder
     */
    public function __construct(ObjectIndexer $objectIndexer, IndexablePersonalVehicleBuilder $indexablePersonalVehicleBuilder)
    {
        $this->objectIndexer = $objectIndexer;
        $this->indexablePersonalVehicleBuilder = $indexablePersonalVehicleBuilder;
    }

    /**
     * @param UserEvent $event
     */
    public function notify(UserEvent $event)
    {
        if(!$event instanceof PersonalUserUpdated) {
            throw new \InvalidArgumentException("IndexUpdatedPersonalUser can only be notified of 'PersonalUserUpdated' events");
        }
        $personalUser = $event->getUser();

        $this->indexUpdatedPersonalUser($personalUser);
    }

}
