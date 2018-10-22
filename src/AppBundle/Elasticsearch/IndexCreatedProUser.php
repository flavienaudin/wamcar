<?php

namespace AppBundle\Elasticsearch;


use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Elasticsearch\Type\IndexableProUser;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\User\Event\ProUserCreated;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;

class IndexCreatedProUser implements UserEventHandler
{
    /** @var ObjectIndexer $objectIndexer */
    private $objectIndexer;

    /**
     * IndexUpdatedProUser constructor.
     * @param ObjectIndexer $objectIndexer
     */
    public function __construct(ObjectIndexer $objectIndexer)
    {
        $this->objectIndexer = $objectIndexer;
    }

    /**
     * @inheritDoc
     */
    public function notify(UserEvent $event)
    {
        if (!$event instanceof ProUserCreated) {
            throw new \InvalidArgumentException("IndexCreatedProUser can only be notified of 'ProUserCreated' events");
        }
        /** @var ProApplicationUser $proUser */
        $proUser = $event->getUser();

        $indexableProUser = IndexableProUser::createFromProApplicationUser($proUser);

        if ($indexableProUser->shouldBeIndexed()) {
            $this->objectIndexer->index($indexableProUser, IndexableProUser::TYPE);
        }
    }
}