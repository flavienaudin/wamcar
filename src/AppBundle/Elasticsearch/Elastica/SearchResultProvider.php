<?php

namespace AppBundle\Elasticsearch\Elastica;


use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Utils\SearchTypeChoice;
use Elastica\Client;
use Elastica\Query;
use Elastica\QueryBuilder;
use Elastica\ResultSet;
use Elastica\Script\AbstractScript;
use Elastica\Script\Script;
use Wamcar\Garage\GarageProUser;
use Wamcar\User\BaseUser;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\Enum\Sorting;

class SearchResultProvider
{
    const LIMIT = 10;
    const OFFSET = 0;
    const MIN_SCORE = 0;

    /** @var EntityIndexer */
    private $searchItemIndexer;
    /** @var EntityIndexer */
    private $personalProjectIndexer;
    /** @var PersonalVehicleEntityIndexer */
    private $personalVehicleEntityIndexer;
    /** @var ProVehicleEntityIndexer */
    private $proVehicleEntityIndexer;
    /** @var Client */
    private $client;

    /**
     * SearchResultProvider constructor.
     * @param EntityIndexer $searchItemIndexer
     * @param EntityIndexer $personalProjectIndexer
     * @param PersonalVehicleEntityIndexer $personalVehicleEntityIndexer
     * @param ProVehicleEntityIndexer $proVehicleEntityIndexer
     * @param Client $client
     */
    public function __construct(EntityIndexer $searchItemIndexer,
                                EntityIndexer $personalProjectIndexer,
                                PersonalVehicleEntityIndexer $personalVehicleEntityIndexer,
                                ProVehicleEntityIndexer $proVehicleEntityIndexer,
                                Client $client)
    {
        $this->searchItemIndexer = $searchItemIndexer;
        $this->personalProjectIndexer = $personalProjectIndexer;
        $this->personalVehicleEntityIndexer = $personalVehicleEntityIndexer;
        $this->proVehicleEntityIndexer = $proVehicleEntityIndexer;
        $this->client = $client;
    }


    /**
     * @param SearchVehicleDTO $searchVehicleDTO
     * @param int $page
     * @return ResultSet
     */
    public function getSearchResult(SearchVehicleDTO $searchVehicleDTO, int $page, ?BaseUser $currentUser = null): ResultSet
    {
        if (empty($searchVehicleDTO->type)) {
            $searchVehicleDTO->type = SearchTypeChoice::getTypeChoice();
        }

        $qb = new QueryBuilder();
        $mainQuery = new Query();

        $mainBoolQuery = $qb->query()->bool();

        // Search types
        if (in_array(SearchTypeChoice::SEARCH_PERSONAL_VEHICLE, $searchVehicleDTO->type)) {
            // Search with reprise ==> Search_Item
            $indexName = $this->searchItemIndexer->getIndexName();
            $typeFilterQuery = null;
            if (count($searchVehicleDTO->type) == 1) {
                $typeFilterQuery = $qb->query()->term(['searchType' => array_first($searchVehicleDTO->type)]);
            } else {
                $typeFilterQuery = $qb->query()->bool();
                foreach ($searchVehicleDTO->type as $type) {
                    $typeFilterQuery->addShould($qb->query()->term(['searchType' => $type]));
                }
            }
            $mainBoolQuery->addFilter($typeFilterQuery);
        } else {
            // Two solutions :
            // 1) search in search_item_index and/or personal_project_item according to $searchVehicleDTO->type selection
            // 2) search in SEARCH_ITEM filtering by vehicle.type and aggregate project.

            // Solution 1)
            if (in_array(SearchTypeChoice::SEARCH_PRO_VEHICLE, $searchVehicleDTO->type)) {
                $indexName = $this->searchItemIndexer->getIndexName();
                if (count($searchVehicleDTO->type) == 1) {
                    $mainBoolQuery->addMust($qb->query()->term(['searchType' => SearchTypeChoice::SEARCH_PRO_VEHICLE]));
                }
            }
            if (in_array(SearchTypeChoice::SEARCH_PERSONAL_PROJECT, $searchVehicleDTO->type)) {
                $indexName = (empty($indexName) ? '' : $indexName . ',') . $this->personalProjectIndexer->getIndexName();
            }
            if (count($searchVehicleDTO->type) > 1) {
                $mainBoolQuery->addShould($qb->query()->term(['searchType' => SearchTypeChoice::SEARCH_PRO_VEHICLE]));
                $mainBoolQuery->addShould($qb->query()->term(['_index' => $this->personalProjectIndexer->getIndexName()]));
            }
        }

        // Handle text query
        if (!empty($searchVehicleDTO->text)) {
            $textBoolQuery = $qb->query()->bool();

            if (in_array(SearchTypeChoice::SEARCH_PRO_VEHICLE, $searchVehicleDTO->type) ||
                in_array(SearchTypeChoice::SEARCH_PERSONAL_VEHICLE, $searchVehicleDTO->type)) {
                $textVehicleMultiMatch = $qb->query()->multi_match();
                $textVehicleMultiMatch->setFields(['vehicle.makeAndModel^2', 'vehicle.make.keyword', 'vehicle.model.keyword', 'vehicle.description']);
                $textVehicleMultiMatch->setOperator(Query\MultiMatch::OPERATOR_AND);
                $textVehicleMultiMatch->setType(Query\MultiMatch::TYPE_CROSS_FIELDS);
                $textVehicleMultiMatch->setQuery($searchVehicleDTO->text);
                $textBoolQuery->addShould($textVehicleMultiMatch);
            }
            if (in_array(SearchTypeChoice::SEARCH_PERSONAL_PROJECT, $searchVehicleDTO->type)) {
                $textMatchProjectDescription = $qb->query()->multi_match();
                $textMatchProjectDescription->setFields(['project.description']);
                $textMatchProjectDescription->setOperator(Query\MultiMatch::OPERATOR_AND);
                $textMatchProjectDescription->setQuery($searchVehicleDTO->text);
                $textBoolQuery->addShould($textMatchProjectDescription);

                $textProjectNested = $qb->query()->nested();
                $textProjectNested->setPath('project.models');

                $textProjectMultiMatch = $qb->query()->multi_match();
                $textProjectMultiMatch->setFields(['project.models.makeAndModel^2', 'project.models.make.keyword', 'project.models.model.keyword']);
                $textProjectMultiMatch->setOperator(Query\MultiMatch::OPERATOR_AND);
                $textProjectMultiMatch->setType(Query\MultiMatch::TYPE_CROSS_FIELDS);
                $textProjectMultiMatch->setQuery($searchVehicleDTO->text);
                $textProjectNested->setQuery($textProjectMultiMatch);

                $textBoolQuery->addShould($textProjectNested);
            }
            $mainBoolQuery->addMust($textBoolQuery);
        }

        // handle location filter
        if (!empty($searchVehicleDTO->latitude) && !empty($searchVehicleDTO->longitude)) {
            $radius = 50;
            if (!empty($searchVehicleDTO->radius)) {
                $radius = $searchVehicleDTO->radius;
            }
            $mainBoolQuery->addFilter($qb->query()->geo_distance('mainSortingLocation',
                ['lat' => $searchVehicleDTO->latitude, 'lon' => $searchVehicleDTO->longitude],
                $radius . 'km'));
        }

        // handle make filter
        if (!empty($searchVehicleDTO->make)) {
            $vehicleMakeFilterQuery = null;
            if (in_array(SearchTypeChoice::SEARCH_PRO_VEHICLE, $searchVehicleDTO->type) ||
                in_array(SearchTypeChoice::SEARCH_PERSONAL_VEHICLE, $searchVehicleDTO->type)) {
                $vehicleMakeFilterQuery = $qb->query()->term(['vehicle.make.keyword' => $searchVehicleDTO->make]);
            }
            $projectMakeFilterQuery = null;
            if (in_array(SearchTypeChoice::SEARCH_PERSONAL_PROJECT, $searchVehicleDTO->type)) {

                $projectMakeFilterQuery = $qb->query()->nested();
                $projectMakeFilterQuery->setPath('project.models');
                $projectMakeFilterQuery->setQuery($qb->query()->term(['project.models.make.keyword' => $searchVehicleDTO->make]));
            }
            if ($vehicleMakeFilterQuery != null && $projectMakeFilterQuery != null) {
                $filterBoolQuery = $qb->query()->bool();
                $filterBoolQuery->addShould($vehicleMakeFilterQuery);
                $filterBoolQuery->addShould($projectMakeFilterQuery);
                $mainBoolQuery->addMust($filterBoolQuery);
            } else {
                $mainBoolQuery->addFilter($vehicleMakeFilterQuery ?? $projectMakeFilterQuery);
            }
        }

        // handle model filter
        if (!empty($searchVehicleDTO->model)) {
            $vehicleModelFilterQuery = null;
            if (in_array(SearchTypeChoice::SEARCH_PRO_VEHICLE, $searchVehicleDTO->type) ||
                in_array(SearchTypeChoice::SEARCH_PERSONAL_VEHICLE, $searchVehicleDTO->type)) {
                $vehicleModelFilterQuery = $qb->query()->term(['vehicle.model.keyword' => $searchVehicleDTO->model]);
            }
            $projectModelFilterQuery = null;
            if (in_array(SearchTypeChoice::SEARCH_PERSONAL_PROJECT, $searchVehicleDTO->type)) {

                $projectModelFilterQuery = $qb->query()->nested();
                $projectModelFilterQuery->setPath('project.models');
                $projectModelFilterQuery->setQuery($qb->query()->term(['project.models.model.keyword' => $searchVehicleDTO->model]));
            }
            if ($vehicleModelFilterQuery != null && $projectModelFilterQuery != null) {
                $filterBoolQuery = $qb->query()->bool();
                $filterBoolQuery->addShould($vehicleModelFilterQuery);
                $filterBoolQuery->addShould($projectModelFilterQuery);
                $mainBoolQuery->addMust($filterBoolQuery);
            } else {
                $mainBoolQuery->addFilter($vehicleModelFilterQuery ?? $projectModelFilterQuery);
            }
        }

        // handle mileage filter
        if (!empty($searchVehicleDTO->mileageMax)) {
            $vehicleMileageFilterQuery = null;
            if (in_array(SearchTypeChoice::SEARCH_PRO_VEHICLE, $searchVehicleDTO->type) ||
                in_array(SearchTypeChoice::SEARCH_PERSONAL_VEHICLE, $searchVehicleDTO->type)) {
                $vehicleMileageFilterQuery = $qb->query()->range('vehicle.mileage', [
                    'lte' => $searchVehicleDTO->mileageMax
                ]);
            }
            $projectMileageFilterQuery = null;
            if (in_array(SearchTypeChoice::SEARCH_PERSONAL_PROJECT, $searchVehicleDTO->type)) {
                $projectMileageFilterQuery = $qb->query()->nested();
                $projectMileageFilterQuery->setPath('project.models');
                $projectMileageFilterQuery->setQuery($qb->query()->range('project.models.mileageMax', [
                    'lte' => $searchVehicleDTO->mileageMax
                ]));
            }
            if ($vehicleMileageFilterQuery != null && $projectMileageFilterQuery != null) {
                $filterBoolQuery = $qb->query()->bool();
                $filterBoolQuery->addShould($vehicleMileageFilterQuery);
                $filterBoolQuery->addShould($projectMileageFilterQuery);
                $mainBoolQuery->addMust($filterBoolQuery);
            } else {
                $mainBoolQuery->addFilter($vehicleMileageFilterQuery ?? $projectMileageFilterQuery);
            }
        }

        // handle year min/max filter
        if (!empty($searchVehicleDTO->yearsMin) || !empty($searchVehicleDTO->yearsMax)) {
            $rangeOpts = [];
            if (!empty($searchVehicleDTO->yearsMin)) {
                $rangeOpts['gte'] = $searchVehicleDTO->yearsMin;
            }
            if (!empty($searchVehicleDTO->yearsMax)) {
                $rangeOpts['lte'] = $searchVehicleDTO->yearsMax;
            }

            $vehicleYearsRangeFilterQuery = null;
            if (in_array(SearchTypeChoice::SEARCH_PRO_VEHICLE, $searchVehicleDTO->type) ||
                in_array(SearchTypeChoice::SEARCH_PERSONAL_VEHICLE, $searchVehicleDTO->type)) {
                $vehicleYearsRangeFilterQuery = $qb->query()->range('vehicle.years', $rangeOpts);
            }
            $projectYearsRangeFilterQuery = null;
            if (in_array(SearchTypeChoice::SEARCH_PERSONAL_PROJECT, $searchVehicleDTO->type)) {
                $projectYearsRangeFilterQuery = $qb->query()->nested();
                $projectYearsRangeFilterQuery->setPath('project.models');
                $projectYearsRangeFilterQuery->setQuery($qb->query()->range('project.models.yearMin', $rangeOpts));
            }
            if ($vehicleYearsRangeFilterQuery != null && $projectYearsRangeFilterQuery != null) {
                $filterBoolQuery = $qb->query()->bool();
                $filterBoolQuery->addShould($vehicleYearsRangeFilterQuery);
                $filterBoolQuery->addShould($projectYearsRangeFilterQuery);
                $mainBoolQuery->addMust($filterBoolQuery);
            } else {
                $mainBoolQuery->addFilter($vehicleYearsRangeFilterQuery ?? $projectYearsRangeFilterQuery);
            }
        }

        // handle budget min/max  filter
        if (!empty($searchVehicleDTO->budgetMin) || !empty($searchVehicleDTO->budgetMax)) {
            $rangeOpts = [];
            if (!empty($searchVehicleDTO->budgetMin)) {
                $rangeOpts['gte'] = $searchVehicleDTO->budgetMin;
            }
            if (!empty($searchVehicleDTO->budgetMax)) {
                $rangeOpts['lte'] = $searchVehicleDTO->budgetMax;
            }

            $vehicleBudgetRangeFilterQuery = null;
            if (in_array(SearchTypeChoice::SEARCH_PRO_VEHICLE, $searchVehicleDTO->type) ||
                in_array(SearchTypeChoice::SEARCH_PERSONAL_VEHICLE, $searchVehicleDTO->type)) {
                $vehicleBudgetRangeFilterQuery = $qb->query()->bool();
                $vehicleBudgetRangeFilterQuery->addShould($qb->query()->range('vehicle.price', $rangeOpts));
                $vehicleBudgetRangeFilterQuery->addShould($qb->query()->range('project.budget', $rangeOpts));
            }
            $projectBudgetRangeFilterQuery = null;
            if (in_array(SearchTypeChoice::SEARCH_PERSONAL_PROJECT, $searchVehicleDTO->type)) {
                $projectBudgetRangeFilterQuery = $qb->query()->range('project.budget', $rangeOpts);

            }
            if ($vehicleBudgetRangeFilterQuery != null && $projectBudgetRangeFilterQuery != null) {
                $filterBoolQuery = $qb->query()->bool();
                $filterBoolQuery->addShould($vehicleBudgetRangeFilterQuery);
                $filterBoolQuery->addShould($projectBudgetRangeFilterQuery);
                $mainBoolQuery->addMust($filterBoolQuery);
            } else {
                $mainBoolQuery->addFilter($vehicleBudgetRangeFilterQuery ?? $projectBudgetRangeFilterQuery);
            }
        }

        // handle transmission filter
        if (!empty($searchVehicleDTO->transmission)) {
            if (in_array(SearchTypeChoice::SEARCH_PRO_VEHICLE, $searchVehicleDTO->type) ||
                in_array(SearchTypeChoice::SEARCH_PERSONAL_VEHICLE, $searchVehicleDTO->type)) {
                $mainBoolQuery->addFilter($qb->query()->term(['vehicle.transmission.keyword' => $searchVehicleDTO->transmission]));
            }
        }

        // handle fuel filter
        if (!empty($searchVehicleDTO->fuel)) {
            if (in_array(SearchTypeChoice::SEARCH_PRO_VEHICLE, $searchVehicleDTO->type) ||
                in_array(SearchTypeChoice::SEARCH_PERSONAL_VEHICLE, $searchVehicleDTO->type)) {
                $mainBoolQuery->addFilter($qb->query()->term(['vehicle.fuel.keyword' => strtolower($searchVehicleDTO->fuel)]));
            }
        }

        $mainQuery->setQuery($mainBoolQuery);
        $mainQuery->setFrom(self::OFFSET + ($page - 1) * self::LIMIT);
        $mainQuery->setSize(self::LIMIT);
        $mainQuery->setExplain(true);
        $mainQuery->setTrackScores();

        // Sorting configuration
        switch ($searchVehicleDTO->sorting) {
            case Sorting::SEARCH_SORTING_DATE:
                if (!empty($searchVehicleDTO->text)) {
                    $mainQuery->setSort(['_score' => 'desc', 'mainSortingDate' => 'desc']);
                } else {
                    $mainQuery->setSort(['mainSortingDate' => 'desc']);
                }
                break;
            case Sorting::SEARCH_SORTING_PRICE_ASC:
                $mainQuery->setSort(['mainSortingPrice' => [
                    'order' => 'asc',
                    'missing' => '_last'
                ], 'mainSortingDate' => 'desc']);
                break;
            case Sorting::SEARCH_SORTING_PRICE_DESC:
                $mainQuery->setSort([
                    'mainSortingPrice' => [
                        'order' => 'desc',
                        'missing' => '_last'
                    ],
                    'mainSortingDate' => 'desc']);
                break;
            case Sorting::SEARCH_SORTING_DISTANCE:
                if (!empty($searchVehicleDTO->latitude) && !empty($searchVehicleDTO->longitude)) {
                    $mainQuery->setSort([
                        "_geo_distance" => [
                            "mainSortingLocation" => [
                                "lat" => floatval($searchVehicleDTO->latitude),
                                "lon" => floatval($searchVehicleDTO->longitude)
                            ],
                            "order" => "asc",
                            "unit" => "km",
                            "ignore_unmapped" => true
                        ]
                    ]);
                }
                break;
            case Sorting::SEARCH_SORTING_RELEVANCE:
                $functionScoreQuery = $qb->query()->function_score();
                $functionScoreQuery->setQuery($mainQuery->getQuery());

                // Combination of the function and the query scores
                $functionScoreQuery->setScoreMode(Query\FunctionScore::SCORE_MODE_SUM);
                // Combination of the functions'scores
                $functionScoreQuery->setBoostMode(Query\FunctionScore::BOOST_MODE_SUM);

                // Query to apply certain score functions to relevant documents
                $vehicleEntityQuery = $qb->query()->bool();
                $vehicleEntityQuery->addShould($qb->query()->term(['searchType' => SearchTypeChoice::SEARCH_PERSONAL_VEHICLE]));
                $vehicleEntityQuery->addShould($qb->query()->term(['searchType' => SearchTypeChoice::SEARCH_PRO_VEHICLE]));

                // Importance : 5/5
                // Script value between [0;1] => factor 5 => [0;5]
                if ($currentUser != null) {

                    $script = new Script("return (params.affinityDegrees[doc.userId.value] ?: params.default) / 100",
                        ["affinityDegrees" => $currentUser->getAffinityDegreesAsArray(), 'default' => 0],
                        AbstractScript::LANG_PAINLESS
                    );
                    $functionScoreQuery->addScriptScoreFunction($script, null, 1);
                }
                $functionScoreQuery->addFieldValueFactorFunction(
                    'vehicle.nbPositiveLikes', 1,
                    Query\FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_SQRT,
                    0, null, $vehicleEntityQuery
                );

                // Value [0;20] => Log1P [0; ~3] => Factor 1 => [0;3]
                $functionScoreQuery->addFieldValueFactorFunction(
                    'vehicle.nbPictures', 1,
                    Query\FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_LN1P,
                    0, 2, $vehicleEntityQuery
                );

                // Google rating [0;5] => factor 1 => [0;5]
                $functionScoreQuery->addFieldValueFactorFunction(
                    'vehicle.googleRating', 1,
                    Query\FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_NONE,
                    0, 1, $qb->query()->term(['searchType' => SearchTypeChoice::SEARCH_PRO_VEHICLE])
                );

                // City [0;3] => factor 3 => [0;3]
                if (!empty($searchVehicleDTO->cityName)) {
                    $functionScoreQuery->addDecayFunction(
                        Query\FunctionScore::DECAY_GAUSS,
                        'mainSortingLocation',
                        floatval($searchVehicleDTO->latitude) . ', ' . floatval($searchVehicleDTO->longitude),
                        '75km',
                        ($searchVehicleDTO->radius / 5) . 'km',
                        0.5,
                        3
                    );
                }

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
                break;
        }


        if (!$mainQuery->hasParam('min_score')) {
            $mainQuery->setMinScore(self::MIN_SCORE);
        }
        return $this->client->getIndex($indexName)->search($mainQuery);
    }

    /**
     * @param BaseUser $user to
     * @param string|null $text
     * @param int $page
     * @param int $limit
     * @return null|ResultSet
     */
    public function getQueryUserVehiclesResult(BaseUser $user, string $text = null, int $page, int $limit = self::LIMIT): ?ResultSet
    {
        if ($user instanceof ProUser) {
            $garageIds = [];
            /** @var GarageProUser $garageMembership */
            foreach ($user->getEnabledGarageMemberships() as $garageMembership) {
                $garageIds[] = $garageMembership->getGarage()->getId();
            }
            return $this->proVehicleEntityIndexer->getQueryGarageVehiclesResult($garageIds, $text, $page, $limit);
        } elseif ($user instanceof PersonalUser) {
            return $this->personalVehicleEntityIndexer->getQueryUserVehicleResult($user->getId(), $text, $page, $limit);
        }
        return null;
    }
}