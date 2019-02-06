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
     * @return ResultSet
     */
    public function getQueryGarageVehiclesResult($garageIds, string $text = null, int $page, int $limit = self::LIMIT): ResultSet
    {
        $mainQuery = new Query();

        $qb = new QueryBuilder();
        $mainBoolQuery = $qb->query()->bool();

        // handle garage filter
        if (is_array($garageIds)) {
            if (count($garageIds) == 0) {
                // No garage given, empty result
                $mainQuery->setQuery($qb->query()->match_none());
                return $this->search($mainQuery);
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
}