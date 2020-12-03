<?php

namespace AppBundle\Elasticsearch\Builder;


use AppBundle\Elasticsearch\Elastica\EntityIndexer;
use AppBundle\Elasticsearch\Type\IndexableSearchItem;
use Wamcar\User\PersonalUser;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\ProVehicle;

class IndexableSearchItemBuilder
{
    /** @var EntityIndexer */
    private $searchItemEntityIndexer;

    /**
     * IndexableSearchItemBuilder constructor.
     * @param EntityIndexer $searchItemEntityIndexer
     */
    public function __construct(EntityIndexer $searchItemEntityIndexer)
    {
        $this->searchItemEntityIndexer = $searchItemEntityIndexer;
    }

    /**
     * @param PersonalUser $personalUser
     * @return array of 'toIndex' and 'toDelete' SearchItem Documents about the personalUser (Vehicle and/or Project)
     */
    public function createSearchItemsFromPersonalUser(PersonalUser $personalUser): array
    {
        $personaUserSearchItemDocuments = [
            'toIndex' => [],
            'toDelete' => []
        ];
        if (count($personalUser->getVehicles()) > 0) {
            /** @var PersonalVehicle $personalVehicule */
            foreach ($personalUser->getVehicles() as $personalVehicule) {
                $indexableSearchItem = new IndexableSearchItem($personalVehicule->getId(), $personalUser->getId());
                $indexableSearchItem->setVehicle(PersonalVehicle::TYPE,
                    $personalVehicule->getDeletedAt(),
                    $personalVehicule->getLatitude(), $personalVehicule->getLongitude(),
                    $personalVehicule->getAdditionalInformation(),
                    $personalVehicule->getMake(),
                    $personalVehicule->getModelName(),
                    $personalVehicule->getFuelName(),
                    $personalVehicule->getTransmission(),
                    $personalVehicule->getYears(),
                    $personalVehicule->getMileage(),
                    null,
                    $personalVehicule->getCreatedAt(),
                    $personalVehicule->getNbPictures(),
                    count($personalVehicule->getPositiveLikes()),
                    null,
                    null,
                    $personalVehicule->getOwner() != null
                );

                if ($personalUser->getProject() != null && !$personalUser->getProject()->isEmpty()) {
                    $indexableSearchItem->setProject($personalUser->getProject());
                }
                if ($indexableSearchItem->shouldBeIndexed()) {
                    $personaUserSearchItemDocuments['toIndex'][] = $this->searchItemEntityIndexer->buildDocument($indexableSearchItem);
                } else {
                    $personaUserSearchItemDocuments['toDelete'][] = $indexableSearchItem->getId();
                }
            }
        } elseif ($personalUser->getProject() != null) {
            $indexableSearchItem = new IndexableSearchItem($personalUser->getProject()->getId(), $personalUser->getId());
            $indexableSearchItem->setProject($personalUser->getProject());

            if ($indexableSearchItem->shouldBeIndexed()) {
                $personaUserSearchItemDocuments['toIndex'][] = $this->searchItemEntityIndexer->buildDocument($indexableSearchItem);
            } else {
                $personaUserSearchItemDocuments['toDelete'][] = $indexableSearchItem->getId();
            }
        }
        return $personaUserSearchItemDocuments;
    }

    /**
     * @param ProVehicle $proVehicle
     * @return IndexableSearchItem
     */
    public function createSearchItemFromProVehicle(ProVehicle $proVehicle): IndexableSearchItem
    {
        $indexableSearchItem = new IndexableSearchItem($proVehicle->getId(), null);
        $indexableSearchItem->setVehicle(ProVehicle::TYPE,
            $proVehicle->getDeletedAt(),
            $proVehicle->getLatitude(), $proVehicle->getLongitude(),
            $proVehicle->getAdditionalInformation(),
            $proVehicle->getMake(),
            $proVehicle->getModelName(),
            $proVehicle->getFuelName(),
            $proVehicle->getTransmission(),
            $proVehicle->getYears(),
            $proVehicle->getMileage(),
            $proVehicle->getPrice(),
            $proVehicle->getCreatedAt(),
            $proVehicle->getNbPictures(),
            count($proVehicle->getPositiveLikes()),
            $proVehicle->getGarage()->getId(),
            $proVehicle->getGarage()->getGoogleRating(),
            count($proVehicle->getSuggestedSellers(false)) > 0
        );
        return $indexableSearchItem;
    }
}