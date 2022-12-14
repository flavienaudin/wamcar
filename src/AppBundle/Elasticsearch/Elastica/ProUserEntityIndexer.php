<?php

namespace AppBundle\Elasticsearch\Elastica;


use AppBundle\Form\DTO\SearchProDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Elastica\Query;
use Elastica\QueryBuilder;
use Elastica\ResultSet;
use Elastica\Script\AbstractScript;
use Elastica\Script\Script;
use Wamcar\User\BaseUser;
use Wamcar\User\ProService;
use Wamcar\Vehicle\Enum\DirectorySorting;

class ProUserEntityIndexer extends EntityIndexer
{

    const LIMIT = 12;
    const MIN_SCORE = 0.1;
    const OFFSET = 0;


    /**
     * @param SearchProDTO $searchProDTO
     * @param int $page
     * @param BaseUser|null $currentUser
     * @param int $limit
     * @return ResultSet
     */
    public function getQueryDirectoryProUserResult(SearchProDTO $searchProDTO, int $page = 1, ?BaseUser $currentUser = null, int $limit = self::LIMIT): ResultSet
    {
        $qb = new QueryBuilder();
        $mainQuery = new Query();
        $mainBoolQuery = $qb->query()->bool();

        // handle text query
        if (!empty($searchProDTO->text)) {
            $textBoolQuery = $qb->query()->bool();

            if($searchProDTO->searchTextInService) {
                $textServicesBoolQuery = $qb->query()->bool();
                $serviceTermQuery = $qb->query()->term(['proServices' => $searchProDTO->text]);
                $textServicesBoolQuery->addShould($serviceTermQuery);

                $specialitiesTermQuery = $qb->query()->term(['proSpecialities' => $searchProDTO->text]);
                $textServicesBoolQuery->addShould($specialitiesTermQuery);

                $textServicesBoolQuery->setMinimumShouldMatch(1);
                $textBoolQuery->addShould($textServicesBoolQuery);
            }

            $textMultiMatchQuery = $qb->query()->multi_match();
            $textMultiMatchQuery->setFields(['firstName', 'lastName', 'description','presentationTitle']);
            $textMultiMatchQuery->setOperator(Query\MultiMatch::OPERATOR_OR);
            $textMultiMatchQuery->setType(Query\MultiMatch::TYPE_CROSS_FIELDS);
            $textMultiMatchQuery->setQuery($searchProDTO->text);
            $textBoolQuery->addShould($textMultiMatchQuery);

            $textNestedGaragesQuery = $qb->query()->nested();
            $textNestedGaragesQuery->setPath('garages');
            $textGarageMatchQuery = $qb->query()->match('garages.garageName', $searchProDTO->text);
            $textNestedGaragesQuery->setQuery($textGarageMatchQuery);
            $textBoolQuery->addShould($textNestedGaragesQuery);

            $mainBoolQuery->addMust($textBoolQuery);
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
            $mainBoolQuery->addFilter($locationNestedGaragesQuery);
        }

        $services = [];
        if ($searchProDTO->filters && count($searchProDTO->filters) > 0) {
            /** @var ArrayCollection $filterValues */
            foreach ($searchProDTO->filters as $filterName => $filterValues) {
                $services[$filterName] = [];
                if($filterValues instanceof Collection) {
                    array_map(function (ProService $selectedProService) use ($filterName, &$services) {
                        $services[$filterName][] = $selectedProService->getName();
                    }, $filterValues->toArray());
                }else{
                    $services[$filterName][] = $filterValues->getName();
                }
            }
            if (count($services) > 0) {
                $servicesBoolQuery = $qb->query()->bool();
                $specialitiesBoolQuery = $qb->query()->bool();
                foreach ($services as $categoryService => $servicesValues) {
                    if(!empty($servicesValues)) {
                        $servicesByFilterBoolQuery = $qb->query()->bool();

                        foreach ($servicesValues as $service) {
                            $serviceTermQuery = $qb->query()->term(['proServices' => $service]);
                            $servicesByFilterBoolQuery->addShould($serviceTermQuery);

                            $specialitiesTermQuery = $qb->query()->term(['proSpecialities' => $service]);
                            $specialitiesBoolQuery->addShould($specialitiesTermQuery);
                        }
                        $servicesByFilterBoolQuery->setMinimumShouldMatch(1);
                        $servicesBoolQuery->addMust($servicesByFilterBoolQuery);
                    }
                }
                if($servicesBoolQuery->count() > 0 ) {
                    $mainBoolQuery->addShould($servicesBoolQuery);

                    $specialitiesBoolQuery->setMinimumShouldMatch(0);
                    $specialitiesBoolQuery->setBoost(2);
                    $mainBoolQuery->addShould($specialitiesBoolQuery);

                }
            }
        }

        if ($mainBoolQuery->count() > 0) {
            $mainQuery->setQuery($mainBoolQuery);
        } else {
            $mainQuery->setQuery($qb->query()->match_all());
        }
        $mainQuery->setFrom(self::OFFSET + ($page - 1) * $limit);
        $mainQuery->setSize($limit);
        $mainQuery->setExplain(true);
        $mainQuery->setTrackScores();

        // Sorting configuration
        if ($searchProDTO->sorting == DirectorySorting::DIRECTORY_SORTING_DISTANCE
            && !empty($searchProDTO->latitude) && !empty($searchProDTO->longitude)) {
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
        } else {
            $functionScoreQuery = $qb->query()->function_score();
            $functionScoreQuery->setQuery($mainQuery->getQuery());

            // Combination of the function and the query scores
            $functionScoreQuery->setScoreMode(Query\FunctionScore::SCORE_MODE_SUM);
            // Combination of the functions'scores
            $functionScoreQuery->setBoostMode(Query\FunctionScore::BOOST_MODE_SUM);

            // Importance : 5/5
            // Script value between [0;10] => factor 1 => [0;10]
            if ($currentUser != null) {
                $script = new Script("return (params.affinityDegrees[doc.id.value] ?: params.default) / 10",
                    ["affinityDegrees" => $currentUser->getAffinityDegreesAsArray(), 'default' => 0],
                    AbstractScript::LANG_PAINLESS
                );
                $functionScoreQuery->addScriptScoreFunction($script, null, 1);
            }

            // Importance : 5/5
            // Google rating [0;5] => factor 1 => [0;5]
            $functionScoreQuery->addFieldValueFactorFunction(
                'maxGaragesGoogleRating',
                1,
                Query\FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_NONE,
                1
            );

            // Importance : 3/5
            // HasAvatar [0;1] => factor 1.25 => [0;1,25]
            $functionScoreQuery->addDecayFunction(
                Query\FunctionScore::DECAY_LINEAR,
                'hasAvatar',
                1, 0.5, 0, 0.5, 1.25
            );

            // Importance : 4/5
            // Description Length [0;1000] => log => [0;3] => factor 1 => [0;3]
            $functionScoreQuery->addFieldValueFactorFunction(
                'descriptionLength',
                1,
                Query\FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_LOG1P,
                0
            );

            $mainQuery->setQuery($functionScoreQuery);
        }
        return $this->search($mainQuery);
    }

    /**
     * Retrieve list of specialities selected by pros.
     * @param string|null $query
     * @return array
     */
    public function getProServices(string $query = null)
    {
        $mainQuery = new Query();
        $qb = new QueryBuilder();

        $proServicesAgg = $qb->aggregation()->terms('proServices');
        $proServicesAgg->setField('proServices');
        $proServicesAgg->setSize(1000);

        if(!empty($query)){
            $proServicesAgg->setInclude(".*".strtolower($query).".*");
        }

        $mainQuery->addAggregation($proServicesAgg);
        $mainQuery->setSize(0);
        $resultSet = $this->search($mainQuery);

        $specialities = array_map(function ($aggDetails) {
            return $aggDetails['key'];
        }, $resultSet->getAggregation('proServices')['buckets']);
        return $specialities;
    }

    /**
     * Retrieve list of specialities selected by pros.
     * @return ResultSet
     */
    public function getSpecialities()
    {
        $mainQuery = new Query();
        $qb = new QueryBuilder();

        $proSpecialitiesAgg = $qb->aggregation()->terms('proSpecialities');
        $proSpecialitiesAgg->setField('proSpecialities');
        $proSpecialitiesAgg->setSize(1000);
        $mainQuery->addAggregation($proSpecialitiesAgg);
        $mainQuery->setSize(0);

        $resultSet = $this->search($mainQuery);

        $specialities = array_map(function ($aggDetails) {
            return $aggDetails['key'];
        }, $resultSet->getAggregation('proSpecialities')['buckets']);
        return $specialities;
    }
}