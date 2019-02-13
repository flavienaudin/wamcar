<?php

namespace AppBundle\Elasticsearch\Elastica;


use AppBundle\Form\DTO\SearchProDTO;
use Elastica\Query;
use Elastica\QueryBuilder;
use Elastica\ResultSet;
use Wamcar\Vehicle\Enum\DirectorySorting;

class ProUserEntityIndexer extends EntityIndexer
{

    const LIMIT = 10;
    const MIN_SCORE = 0.1;
    const OFFSET = 0;


    public function getQueryDirectoryProUserResult(SearchProDTO $searchProDTO, int $page = 1, int $limit = self::LIMIT): ResultSet
    {
        $mainQuery = new Query();

        $qb = new QueryBuilder();
        $mainBoolQuery = $qb->query()->bool();

        // handle text query
        if (!empty($searchProDTO->text)) {
            $textMultiMatchQuery = $qb->query()->multi_match();
            $textMultiMatchQuery->setFields(['firstName', 'lastName', 'description']);
            $textMultiMatchQuery->setOperator(Query\MultiMatch::OPERATOR_OR);
            $textMultiMatchQuery->setType(Query\MultiMatch::TYPE_CROSS_FIELDS);
            $textMultiMatchQuery->setQuery($searchProDTO->text);
            $mainBoolQuery->addShould($textMultiMatchQuery);

            $textNestedGaragesQuery = $qb->query()->nested();
            $textNestedGaragesQuery->setPath('garages');
            $textGarageMultiMatchQuery = $qb->query()->multi_match();
            $textGarageMultiMatchQuery->setFields(['garages.garageName', 'garages.garagePresentation']);
            $textGarageMultiMatchQuery->setOperator(Query\MultiMatch::OPERATOR_OR);
            $textGarageMultiMatchQuery->setType(Query\MultiMatch::TYPE_CROSS_FIELDS);
            $textGarageMultiMatchQuery->setQuery($searchProDTO->text);
            $textNestedGaragesQuery->setQuery($textGarageMultiMatchQuery);
            $mainBoolQuery->addShould($textNestedGaragesQuery);
        }

        // handle location filter
        if (!empty($searchProDTO->latitude) && !empty($searchProDTO->longitude)) {
            $radius = 50;
            if (!empty($searchProDTO->radius)) {
                $radius = $searchProDTO->radius;
            }

            $locationNestedGaragesQuery = $qb->query()->nested();
            $locationNestedGaragesQuery->setPath('garages');
            $locationNestedGaragesQuery->setQuery($qb->query()->geo_distance('garages.garageLocation',
                ['lat' => $searchProDTO->latitude, 'lon' => $searchProDTO->longitude],
                $radius . 'km'));
            $mainBoolQuery->addShould($locationNestedGaragesQuery);
        }

        if (empty($searchProDTO->text) && (empty($searchProDTO->latitude) || empty($searchProDTO->longitude))) {
            $mainQuery->setQuery($qb->query()->match_all());
        } else {
            $mainQuery->setQuery($mainBoolQuery);
        }
        $mainQuery->setFrom(self::OFFSET + ($page - 1) * $limit);
        $mainQuery->setSize($limit);
        $mainQuery->setExplain(true);
        $mainQuery->setTrackScores();

        // Sorting configuration
        switch ($searchProDTO->sorting) {
            case DirectorySorting::DIRECTORY_SORTING_DISTANCE:
                if (!empty($searchProDTO->latitude) && !empty($searchProDTO->longitude)) {
                    $mainQuery->setSort([
                        '_geo_distance' => [
                            'garages.garageLocation' => [
                                "lat" => floatval($searchProDTO->latitude),
                                "lon" => floatval($searchProDTO->longitude)
                            ],
                            'nested' => [
                                'path' => 'garages'
                            ],
                            "order" => "asc",
                            "unit" => "km",
                            "ignore_unmapped" => true
                        ]
                    ]);

                    break;
                }
            // default sorting by RELEVANCE below :
            case DirectorySorting::DIRECTORY_SORTING_RELEVANCE:
            default:
                $functionScoreQuery = $qb->query()->function_score();
                $functionScoreQuery->setQuery($mainQuery->getQuery());

                // Combination of the function and the query scores
                $functionScoreQuery->setScoreMode(Query\FunctionScore::SCORE_MODE_SUM);
                // Combination of the functions'scores
                $functionScoreQuery->setBoostMode(Query\FunctionScore::BOOST_MODE_REPLACE);

                // Importance : 2
                $functionScoreQuery->addFieldValueFactorFunction(
                    'maxGaragesGoogleRating',
                    1,
                    Query\FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_NONE,
                    1
                );

                // Importance : 2
                $functionScoreQuery->addDecayFunction(
                    Query\FunctionScore::DECAY_LINEAR,
                    'hasAvatar',
                    1, 0.5, 0, 0.5, 1.25
                );

                // Importance : 2
                $functionScoreQuery->addFieldValueFactorFunction(
                    'descriptionLength',
                    1,
                    Query\FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_LOG1P,
                    0
                );

                // Importance : 5
                $functionScoreQuery->addFieldValueFactorFunction(
                    "_score",
                    1.5,
                    Query\FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_SQUARE,
                    1
                );

                $mainQuery->setQuery($functionScoreQuery);
                break;
        }
        return $this->search($mainQuery);
    }
}