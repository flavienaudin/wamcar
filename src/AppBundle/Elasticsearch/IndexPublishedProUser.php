<?php

namespace AppBundle\Elasticsearch;


use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder;
use AppBundle\Elasticsearch\Elastica\ProVehicleEntityIndexer;
use Wamcar\User\Event\ProUserPublished;
use Wamcar\User\Event\ProUserUnpublished;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;
use Wamcar\Vehicle\ProVehicle;

class IndexPublishedProUser implements UserEventHandler
{
    /** @var ProVehicleEntityIndexer $proVehicleEntityIndexer */
    private $proVehicleEntityIndexer;

    /** @var IndexableProVehicleBuilder $indexableProVehicleBuilder */
    private $indexableProVehicleBuilder;

    /**
     * IndexPublishedProUser constructor.
     * @param ProVehicleEntityIndexer $proVehicleEntityIndexer
     * @param IndexableProVehicleBuilder $indexableProVehicleBuilder
     */
    public function __construct(ProVehicleEntityIndexer $proVehicleEntityIndexer, IndexableProVehicleBuilder $indexableProVehicleBuilder)
    {
        $this->proVehicleEntityIndexer = $proVehicleEntityIndexer;
        $this->indexableProVehicleBuilder = $indexableProVehicleBuilder;
    }

    /**
     * @inheritDoc
     */
    public function notify(UserEvent $event)
    {
        if (!$event instanceof ProUserPublished && !$event instanceof ProUserUnpublished) {
            throw new \InvalidArgumentException("IndexPublishedProUser can only be notified of 'ProUserPublished ', 'ProUserUnPublished ' events");
        }
        /** @var ProApplicationUser $proUser */
        $proUser = $event->getUser();

        if ($proUser->isPublishable()) {
            $indexableProVehiclesDocuments = $proUser->getVehicles()->map(function (ProVehicle $proVehicle) {
                return $this->proVehicleEntityIndexer->buildDocument($this->indexableProVehicleBuilder->buildFromVehicle($proVehicle));
            })->toArray();
            $this->proVehicleEntityIndexer->indexAllDocuments($indexableProVehiclesDocuments, true);
        } else {
            $indexableProVehiclesIds = $proUser->getVehicles()->map(function (ProVehicle $proVehicle) {
                return $proVehicle->getId();
            })->toArray();
            $this->proVehicleEntityIndexer->deleteByIds($indexableProVehiclesIds);
        }
    }

}