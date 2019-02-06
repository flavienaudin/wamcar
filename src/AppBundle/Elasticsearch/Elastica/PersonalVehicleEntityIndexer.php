<?php

namespace AppBundle\Elasticsearch\Elastica;


use Elastica\Query;
use Elastica\QueryBuilder;
use Elastica\ResultSet;

class PersonalVehicleEntityIndexer extends EntityIndexer
{
    const LIMIT = 10;
    const OFFSET = 0;

    /**
     * @param int $userId
     * @param string|null $text
     * @param int $page
     * @param int $limit
     * @return ResultSet
     */
    public function getQueryUserVehicleResult(int $userId, string $text = null, int $page, int $limit = self::LIMIT): ResultSet
    {
        $mainQuery = new Query();

        $qb = new QueryBuilder();
        $mainBoolQuery = $qb->query()->bool();

        // handle user filter
        $mainBoolQuery->addFilter($qb->query()->term(['userId' => $userId]));

        //handle query filter
        if (!empty($text)) {
            $textMultiMatchQuery = $qb->query()->multi_match();
            $textMultiMatchQuery->setFields(['makeAndModel^2', 'make', 'model', 'engine', 'description']);
            $textMultiMatchQuery->setOperator(Query\MultiMatch::OPERATOR_OR);
            $textMultiMatchQuery->setType(Query\MultiMatch::TYPE_CROSS_FIELDS);
            $textMultiMatchQuery->setQuery($text);
            $mainBoolQuery->addShould($textMultiMatchQuery);
            $mainQuery->setMinScore(1);
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