<?php

namespace AppBundle\Elasticsearch;


use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Elasticsearch\Elastica\ProUserEntityIndexer;
use AppBundle\Elasticsearch\Type\IndexableProUser;
use Wamcar\Garage\Event\GarageMemberAssignedEvent;
use Wamcar\Garage\Event\GarageMemberUnassignedEvent;
use Wamcar\User\Event\ProUserCreated;
use Wamcar\User\Event\ProUserRemoved;
use Wamcar\User\Event\ProUserUpdated;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;

class IndexUpdatedProUser implements UserEventHandler
{
    /** @var ProUserEntityIndexer $proUserEntityIndexer */
    private $proUserEntityIndexer;


    /**
     * IndexUpdatedProUser constructor.
     * @param ProUserEntityIndexer $proUserEntityIndexer
     */
    public function __construct(ProUserEntityIndexer $proUserEntityIndexer)
    {
        $this->proUserEntityIndexer = $proUserEntityIndexer;
    }

    /**
     * @inheritDoc
     */
    public function notify(UserEvent $event)
    {
        if (!$event instanceof ProUserCreated && !$event instanceof ProUserUpdated && !$event instanceof ProUserRemoved
            && !$event instanceof GarageMemberAssignedEvent && !$event instanceof GarageMemberUnassignedEvent
        ) {
            throw new \InvalidArgumentException("IndexUpdatedProUser can only be notified of 'ProUserCreated', 'ProUserUpdated', 'ProUserRemoved', 'GarageMemberAssignedEvent' or 'GarageMemberUnassignedEvent' events");
        }
        /** @var ProApplicationUser $proUser */
        $proUser = $event->getUser();
        $this->proUserEntityIndexer->updateIndexable(IndexableProUser::createFromProApplicationUser($proUser));
    }

}