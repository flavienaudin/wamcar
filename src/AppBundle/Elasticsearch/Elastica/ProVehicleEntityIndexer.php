<?php

namespace AppBundle\Elasticsearch\Elastica;


use Elastica\Query;
use Elastica\QueryBuilder;
use Elastica\ResultSet;

class ProVehicleEntityIndexer extends EntityIndexer
{
    const LIMIT = 10;
    const MIN_SCORE = 0.5;
    const OFFSET = 0;

    /**
     * @param $garageIds
     * @param string|null $text
     * @param int $page
     * @param int $limit
     * @return null|ResultSet
     */
    public function getQueryGarageVehiclesResult($garageIds, string $text = null, int $page, int $limit = self::LIMIT): ?ResultSet
    {
        $mainQuery = new Query();

        $qb = new QueryBuilder();
        $mainBoolQuery = $qb->query()->bool();

        // handle garage filter
        if (is_array($garageIds)) {
            if (count($garageIds) == 0) {
                // No garage given, empty result
                return null;
            } else {
                foreach ($garageIds as $garageId) {
                    $mainBoolQuery->addShould($qb->query()->term(['garageId' => $garageId]));
                }
            }
        } else {
            $mainBoolQuery->addFilter($qb->query()->term(['garageId' => $garageIds]));
        }
        if (!empty($text)) {
            $textMultiMatchQuery = $qb->query()->multi_match();
            $textMultiMatchQuery->setFields(['makeAndModel^2', 'make', 'model', 'engine', 'description']);
            $textMultiMatchQuery->setOperator(Query\MultiMatch::OPERATOR_OR);
            $textMultiMatchQuery->setType(Query\MultiMatch::TYPE_CROSS_FIELDS);
            $textMultiMatchQuery->setQuery($text);
            $mainBoolQuery->addShould($textMultiMatchQuery);
            $mainQuery->setMinScore(1.1);
        }
        $mainQuery->setQuery($mainBoolQuery);
        $mainQuery->setFrom(self::OFFSET + ($page - 1) * $limit);
        $mainQuery->setSize($limit);
        $mainQuery->setExplain(true);
        $mainQuery->setTrackScores();

        // Sorting Configuration
        if (empty($text)) {
            $mainQuery->setSort(['mainSortingDate' => 'desc']);
        } else {
            $mainQuery->setSort(['_score' => 'desc', 'mainSortingDate' => 'desc']);
        }
        return $this->search($mainQuery);
    }


    /**
     * @param int $sellerId
     * @param string|null $text
     * @param int $page
     * @param int $limit
     * @return null|ResultSet
     */
    public function getQueryVehiclesByProUserResult(int $sellerId, string $text = null, int $page, int $limit = self::LIMIT): ?ResultSet
    {
        $mainQuery = new Query();

        $qb = new QueryBuilder();
        $mainBoolQuery = $qb->query()->bool();

        // handle seller filter
        $mainBoolQuery->addFilter($qb->query()->term(['sellerId' => $sellerId]));
        if (!empty($text)) {
            $textMultiMatchQuery = $qb->query()->multi_match();
            $textMultiMatchQuery->setFields(['makeAndModel^2', 'make', 'model', 'engine', 'description']);
            $textMultiMatchQuery->setOperator(Query\MultiMatch::OPERATOR_OR);
            $textMultiMatchQuery->setType(Query\MultiMatch::TYPE_CROSS_FIELDS);
            $textMultiMatchQuery->setQuery($text);
            $mainBoolQuery->addShould($textMultiMatchQuery);
            $mainQuery->setMinScore(1.1);
        }
        $mainQuery->setQuery($mainBoolQuery);
        $mainQuery->setFrom(self::OFFSET + ($page - 1) * $limit);
        $mainQuery->setSize($limit);
        $mainQuery->setExplain(true);
        $mainQuery->setTrackScores();

        // Sorting Configuration
        if (empty($text)) {
            $mainQuery->setSort(['mainSortingDate' => 'desc']);
        } else {
            $mainQuery->setSort(['_score' => 'desc', 'mainSortingDate' => 'desc']);
        }


        return $this->search($mainQuery);
    }
}