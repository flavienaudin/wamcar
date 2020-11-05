<?php

namespace AppBundle\Elasticsearch;


use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Elasticsearch\Builder\IndexableProVehicleBuilder;
use AppBundle\Elasticsearch\Builder\IndexableSearchItemBuilder;
use AppBundle\Elasticsearch\Elastica\EntityIndexer;
use AppBundle\Elasticsearch\Elastica\ProUserEntityIndexer;
use AppBundle\Elasticsearch\Elastica\ProVehicleEntityIndexer;
use AppBundle\Elasticsearch\Traits\ProVehicleIndexerTrait;
use AppBundle\Elasticsearch\Type\IndexableProUser;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use Wamcar\Garage\Event\GarageMemberAssignedEvent;
use Wamcar\Garage\Event\GarageMemberUnassignedEvent;
use Wamcar\Garage\Garage;
use Wamcar\User\Event\ProUserCreated;
use Wamcar\User\Event\ProUserRemoved;
use Wamcar\User\Event\ProUserUpdated;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;
use Wamcar\User\UserRepository;

class IndexUpdatedProUser implements UserEventHandler
{
    use ProVehicleIndexerTrait {
        ProVehicleIndexerTrait::__construct as private __tConstruct;
    }

    /** @var ProUserEntityIndexer $proUserEntityIndexer */
    private $proUserEntityIndexer;

    /** @var UserRepository $userRepository */
    private $userRepository;

    /** @var ProVehicleEditionService $proVehicleEditionService */
    private $proVehicleEditionService;

    /**
     * IndexUpdatedProUser constructor.
     * @param ProVehicleEntityIndexer $proVehicleEntityIndexer
     * @param IndexableProVehicleBuilder $indexableProVehicleBuilder
     * @param EntityIndexer $searchItemEntityIndexer
     * @param IndexableSearchItemBuilder $indexableSearchItemBuilder
     * @param ProUserEntityIndexer $proUserEntityIndexer
     * @param UserRepository $userRepository
     * @param ProVehicleEditionService $proVehicleEditionService
     */
    public function __construct(ProUserEntityIndexer $proUserEntityIndexer,
                                UserRepository $userRepository,
                                ProVehicleEditionService $proVehicleEditionService,
                                ProVehicleEntityIndexer $proVehicleEntityIndexer,
                                IndexableProVehicleBuilder $indexableProVehicleBuilder,
                                EntityIndexer $searchItemEntityIndexer,
                                IndexableSearchItemBuilder $indexableSearchItemBuilder
    )
    {
        $this->__tConstruct($proVehicleEntityIndexer, $indexableProVehicleBuilder,
            $searchItemEntityIndexer, $indexableSearchItemBuilder);
        $this->proUserEntityIndexer = $proUserEntityIndexer;
        $this->userRepository = $userRepository;
        $this->proVehicleEditionService = $proVehicleEditionService;
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

        $result = $this->proUserEntityIndexer->updateIndexable(IndexableProUser::createFromProApplicationUser($proUser));

        if ($result['indexed']) {
            // ProUser indexé
            if ($proUser->getPublishedAt() == null) {
                // ProUser non publié => publié
                $proUser->setPublishedAt(new \DateTime());
                $proUser->setUnpublishedAt(null);
                $this->userRepository->update($proUser);

                /** @var Garage $garage */
                foreach ($proUser->getGarages() as $garage) {
                    if(count($garage->getAvailableSellers()) == 1){ // Premier vendeur disponible
                        $indexableProVehiclesDocumentsToIndex = [];
                        $indexableProVehiclesIdsToDelete = [];
                        foreach ($garage->getProVehicles() as $proVehicle ){
                            $indexableProVehicle = $this->indexableProVehicleBuilder->buildFromVehicle($proVehicle);
                            if($indexableProVehicle->shouldBeIndexed()) {
                                $indexableProVehiclesDocumentsToIndex[] = $this->proUserEntityIndexer->buildDocument($indexableProVehicle);
                            }else{
                                $indexableProVehiclesIdsToDelete[] = $indexableProVehicle->getId();
                            }
                        }
                        $this->proVehicleEntityIndexer->indexAllDocuments($indexableProVehiclesDocumentsToIndex, true);
                        if(count($indexableProVehiclesIdsToDelete) > 0) {
                            $this->proVehicleEntityIndexer->deleteByIds($indexableProVehiclesIdsToDelete);
                        }

                        $indexableProVehicleSearchItemsDocumentsToIndex = [];
                        $indexableProVehicleSearchItemsIdsToDelete = [];
                        foreach($garage->getProVehicles() as $proVehicle) {
                            $indexableSearchItem = $this->indexableSearchItemBuilder->createSearchItemFromProVehicle($proVehicle);
                            if($indexableSearchItem->shouldBeIndexed()) {
                                $indexableProVehicleSearchItemsDocumentsToIndex[] = $this->searchItemEntityIndexer->buildDocument($indexableSearchItem);
                            }else{
                                $indexableProVehicleSearchItemsIdsToDelete[] = $indexableSearchItem->getId();
                            }
                        };
                        $this->searchItemEntityIndexer->indexAllDocuments($indexableProVehicleSearchItemsDocumentsToIndex, true);
                        if(count($indexableProVehicleSearchItemsIdsToDelete)> 0) {
                            $this->searchItemEntityIndexer->deleteByIds($indexableProVehicleSearchItemsIdsToDelete);
                        }
                    }
                }
            }
        } else {
            // ProUser non indexé
            if ($proUser->getPublishedAt() != null) {
                // ProUser publié => dépublié
                $proUser->setPublishedAt(null);
                $proUser->setUnpublishedAt(new \DateTime());
                $this->userRepository->update($proUser);

                // Gestion des véhicules
                /** @var Garage $garage */
                foreach ($proUser->getGarages() as $garage) {
                    if(count($garage->getAvailableSellers()) == 0){
                        foreach ($garage->getProVehicles() as $proVehicle) {
                            // MaJ ES ProVehicule pour le déréférencer car pas de vendeur disponible
                            $this->indexProVehicle($proVehicle);
                        }
                    }
                }
            }
        }
    }

}