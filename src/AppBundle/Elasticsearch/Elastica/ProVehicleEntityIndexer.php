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
    public function getQueryGarageVehiclesResult($garageIds, ?string $text, int $page, int $limit = self::LIMIT): ?ResultSet
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
            $mainBoolQuery->addMust($textMultiMatchQuery);
        }
        $mainQuery->setQuery($mainBoolQuery);
        $mainQuery->setFrom(self::OFFSET + ($page - 1) * $limit);
        $mainQuery->setSize($limit);
        $mainQuery->setExplain(true);
        $mainQuery->setTrackScores();

        // Sorting Configuration
        $functionScoreQuery = $qb->query()->function_score();
        $functionScoreQuery->setQuery($mainQuery->getQuery());

        // Combination of the function and the query scores
        $functionScoreQuery->setScoreMode(Query\FunctionScore::SCORE_MODE_SUM);
        // Combination of the functions'scores
        $functionScoreQuery->setBoostMode(Query\FunctionScore::BOOST_MODE_SUM);

        // Value [0;20] => Log1P [0; ~3] => Factor 1 => [0;3]
        $functionScoreQuery->addFieldValueFactorFunction(
            'nbPicture', 1,
            Query\FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_LN1P,
            0, 2
        );

        // Date [0;1] => factor 2 => [0;2]
        $functionScoreQuery->addDecayFunction(
            Query\FunctionScore::DECAY_GAUSS,
            'mainSortingDate',
            'now',
            '100d',
            '1d',
            0.3,
            2
        );
        $mainQuery->setQuery($functionScoreQuery);
        $mainQuery->setSort(['_score' => 'desc', 'mainSortingDate' => 'desc']);

        $result = $this->search($mainQuery);
        dump($result->getResults());
        return $result;
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
        $functionScoreQuery = $qb->query()->function_score();
        $functionScoreQuery->setQuery($mainQuery->getQuery());
        // Combination of the function and the query scores
        $functionScoreQuery->setScoreMode(Query\FunctionScore::SCORE_MODE_SUM);
        // Combination of the functions'scores
        $functionScoreQuery->setBoostMode(Query\FunctionScore::BOOST_MODE_SUM);

        // Value [0;20] => Log1P [0; ~3] => Factor 1 => [0;3]
        $functionScoreQuery->addFieldValueFactorFunction(
            'nbPicture', 1,
            Query\FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_LN1P,
            0, 2
        );

        // Date [0;1] => factor 2 => [0;2]
        $functionScoreQuery->addDecayFunction(
            Query\FunctionScore::DECAY_GAUSS,
            'mainSortingDate',
            'now',
            '100d',
            '1d',
            0.3,
            2
        );
        $mainQuery->setQuery($functionScoreQuery);
        $mainQuery->setSort(['_score' => 'desc', 'mainSortingDate' => 'desc']);


        return $this->search($mainQuery);
    }
}