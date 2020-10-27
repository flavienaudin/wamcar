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
use AppBundle\Exception\Vehicle\NewSellerToAssignNotFoundException;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use Wamcar\Garage\Event\GarageMemberAssignedEvent;
use Wamcar\Garage\Event\GarageMemberUnassignedEvent;
use Wamcar\User\Event\ProUserCreated;
use Wamcar\User\Event\ProUserRemoved;
use Wamcar\User\Event\ProUserUpdated;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;
use Wamcar\User\UserRepository;
use Wamcar\Vehicle\ProVehicle;

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

                $indexableProVehiclesDocuments = $proUser->getVehicles()->map(function (ProVehicle $proVehicle) {
                    return $this->proVehicleEntityIndexer->buildDocument($this->indexableProVehicleBuilder->buildFromVehicle($proVehicle));
                })->toArray();
                $this->proVehicleEntityIndexer->indexAllDocuments($indexableProVehiclesDocuments, true);
            }
        } else {
            // ProUser non indexé
            if ($proUser->getPublishedAt() != null) {
                // ProUser publié => dépublié
                $proUser->setPublishedAt(null);
                $proUser->setUnpublishedAt(new \DateTime());
                $this->userRepository->update($proUser);

                // Gestion des véhicules
                foreach ($proUser->getVehicles() as $proVehicle) {
                    try {
                        $this->proVehicleEditionService->assignSeller($proVehicle);
                    } catch (\InvalidArgumentException|NewSellerToAssignNotFoundException $newSellerToAssignNotFoundException) {
                        // MaJ ES ProVehicule pour le déréférencer car pas de nouveau vendeur pour ré-affectation
                        $this->indexProVehicle($proVehicle);
                    }
                }
            }
        }
    }

}