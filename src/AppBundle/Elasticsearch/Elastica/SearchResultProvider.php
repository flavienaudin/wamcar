<?php

namespace AppBundle\Elasticsearch\Elastica;


use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Utils\SearchTypeChoice;
use Elastica\Client;
use Elastica\Query;
use Elastica\QueryBuilder;
use Elastica\Result;
use Elastica\ResultSet;
use Elastica\Script\AbstractScript;
use Elastica\Script\Script;
use Psr\Log\LoggerInterface;
use Wamcar\Garage\GarageProUser;
use Wamcar\User\BaseUser;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\Enum\Sorting;

class SearchResultProvider
{
    const LIMIT = 10;
    const OFFSET = 0;
    const MIN_SCORE = 0.5;

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
    /** @var LoggerInterface */
    private $logger;

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
                                Client $client, LoggerInterface $logger)
    {
        $this->searchItemIndexer = $searchItemIndexer;
        $this->personalProjectIndexer = $personalProjectIndexer;
        $this->personalVehicleEntityIndexer = $personalVehicleEntityIndexer;
        $this->proVehicleEntityIndexer = $proVehicleEntityIndexer;
        $this->client = $client;
        $this->logger = $logger;
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

        // With Pictures or without vehicle.nbPicture field (ie PersonalProject)
        $withPictureBoolQuery = $qb->query()->bool();
        $withoutNbPicturesBoolQuery = $qb->query()->bool();
        $withoutNbPicturesBoolQuery->addMustNot($qb->query()->exists('vehicle.nbPictures'));
        $withPictureBoolQuery->addShould($withoutNbPicturesBoolQuery);
        $withPictureBoolQuery->addShould($qb->query()->range('vehicle.nbPictures', ['gt' => 0]));
        //$mainBoolQuery->addFilter($withPictureBoolQuery);

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
                $mainBoolQuery->setMinimumShouldMatch(1);
            }
        }

        // Handle text query
        if (!empty($searchVehicleDTO->text)) {
            $textBoolQuery = $qb->query()->bool();

            $queryTermsAsArray = explode(' ', $searchVehicleDTO->text);
            $filteredQueryTerms = array_filter($queryTermsAsArray, function ($term) {
                return strlen($term) > 2;
            });

            if (in_array(SearchTypeChoice::SEARCH_PRO_VEHICLE, $searchVehicleDTO->type) ||
                in_array(SearchTypeChoice::SEARCH_PERSONAL_VEHICLE, $searchVehicleDTO->type)) {

                // Make
                $textMakeMultiMatch = $qb->query()->multi_match();
                $textMakeMultiMatch->setFields(['vehicle.make', 'vehicle.make.keyword^2', 'vehicle.makeAndModel']);
                $textMakeMultiMatch->setOperator(Query\MultiMatch::OPERATOR_OR);
                $textMakeMultiMatch->setType(Query\MultiMatch::TYPE_MOST_FIELDS);
                $textMakeMultiMatch->setTieBreaker(0.75);
                $textMakeMultiMatch->setQuery($searchVehicleDTO->text);
                $textBoolQuery->addShould($textMakeMultiMatch);

                // Model
                $textModelMatch = $qb->query()->multi_match();
                $textModelMatch->setFields(['vehicle.model', 'vehicle.model.keyword^2', 'vehicle.makeAndModel']);
                $textModelMatch->setOperator(Query\MultiMatch::OPERATOR_OR);
                $textModelMatch->setType(Query\MultiMatch::TYPE_MOST_FIELDS);
                $textModelMatch->setTieBreaker(0.75);
                $textModelMatch->setQuery($searchVehicleDTO->text);
                $textBoolQuery->addShould($textModelMatch);

                // Description
                if (!empty($filteredQueryTerms)) {
                    $textModelMatch = $qb->query()->match();
                    $textModelMatch->setFieldQuery('vehicle.description', join(' ', $filteredQueryTerms));
                    $textBoolQuery->addShould($textModelMatch);
                }
            }

            if (in_array(SearchTypeChoice::SEARCH_PERSONAL_PROJECT, $searchVehicleDTO->type)) {
                // Description
                if (!empty($filteredQueryTerms)) {
                    $textProjectDescriptionMatch = $qb->query()->match();
                    $textProjectDescriptionMatch->setFieldQuery('project.description', join(' ', $filteredQueryTerms));
                    $textBoolQuery->addShould($textProjectDescriptionMatch);
                }

                $textProjectNested = $qb->query()->nested();
                $textProjectNested->setPath('project.models');

                $textProjectNestedBoolQuery = $qb->query()->bool();

                // Make
                $textProjectMakeMultiMatch = $qb->query()->multi_match();
                $textProjectMakeMultiMatch->setFields(['project.models.make', 'project.models.make.keyword^2', 'project.models.makeAndModel']);
                $textProjectMakeMultiMatch->setOperator(Query\MultiMatch::OPERATOR_OR);
                $textProjectMakeMultiMatch->setType(Query\MultiMatch::TYPE_MOST_FIELDS);
                $textProjectMakeMultiMatch->setTieBreaker(0.75);
                $textProjectMakeMultiMatch->setQuery($searchVehicleDTO->text);
                $textProjectNestedBoolQuery->addShould($textProjectMakeMultiMatch);

                // Model
                $textModelModelMultiMatch = $qb->query()->multi_match();
                $textModelModelMultiMatch->setFields(['project.models.model', 'project.models.model.keyword^2', 'project.models.makeAndModel']);
                $textModelModelMultiMatch->setOperator(Query\MultiMatch::OPERATOR_OR);
                $textModelModelMultiMatch->setType(Query\MultiMatch::TYPE_MOST_FIELDS);
                $textModelModelMultiMatch->setTieBreaker(0.75);
                $textModelModelMultiMatch->setQuery($searchVehicleDTO->text);
                $textProjectNestedBoolQuery->addShould($textModelModelMultiMatch);
                $textProjectNested->setQuery($textProjectNestedBoolQuery);

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
                $mainQuery->setSort(['mainSortingDate' => 'desc']);
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

                // WamAffinity : Importance : 5/5
                // Script value between [0;1] => factor 5 => [0;5]
                if ($currentUser != null) {
                    $script = new Script("return (params.affinityDegrees[doc.userId.value] ?: params.default) / 100",
                        ["affinityDegrees" => $currentUser->getAffinityDegreesAsArray(), 'default' => 0],
                        AbstractScript::LANG_PAINLESS
                    );
                    $functionScoreQuery->addScriptScoreFunction($script, null, 1);
                }

                // Nb likes positifs : Value [0; 5] => Sqrt [0;2,23] => Factor 0 => [ 0;2,23]
                $functionScoreQuery->addFieldValueFactorFunction(
                    'vehicle.nbPositiveLikes', 1,
                    Query\FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_SQRT,
                    0, null, $vehicleEntityQuery
                );

                // Nb Photos : Value [0;20] => LogNep1P [0; ~3] => Factor 1 => [0;3]
                $functionScoreQuery->addFieldValueFactorFunction(
                    'vehicle.nbPictures', 1,
                    Query\FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_LN1P,
                    -2, 2, $vehicleEntityQuery
                );

                // Google rating [0;5] => factor 1 => [0;5]
                $functionScoreQuery->addFieldValueFactorFunction(
                    'vehicle.googleRating', 1,
                    Query\FunctionScore::FIELD_VALUE_FACTOR_MODIFIER_NONE,
                    0, 1, $qb->query()->term(['searchType' => SearchTypeChoice::SEARCH_PRO_VEHICLE])
                );

                // City [0;1] => factor 3 => [0;3]
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
                $mainSortingDateWeight = 2;
                if (empty($searchVehicleDTO->text)) {
                    // Date [0;1] => factor 6 => [0;6]
                    $mainSortingDateWeight = 6;
                }
                $functionScoreQuery->addDecayFunction(
                    Query\FunctionScore::DECAY_GAUSS,
                    'mainSortingDate',
                    'now',
                    '100d',
                    '1d',
                    0.3,
                    $mainSortingDateWeight
                );

                $mainQuery->setQuery($functionScoreQuery);


                if (in_array(SearchTypeChoice::SEARCH_PRO_VEHICLE, $searchVehicleDTO->type)
                    && count($searchVehicleDTO->type) === 1) {
                    // Search only in PRO_VEHICLE : Google Rating & many pictures, increasse score => higher minScore
                    if (empty($searchVehicleDTO->text)) {
                        $mainQuery->setMinScore(4);
                    } else {
                        $queryTermsAsArray = explode(' ', $searchVehicleDTO->text);
                        $mainQuery->setMinScore(8 * sqrt(count($queryTermsAsArray)));
                    }
                } elseif (!in_array(SearchTypeChoice::SEARCH_PRO_VEHICLE, $searchVehicleDTO->type)) {
                    // Search only Personal Vehicle OR/AND Project : no additionnal function score => low score => lower minScore
                    $mainQuery->setMinScore(0);
                } else {
                    // Mix Search : Pro / Personal
                    if (empty($searchVehicleDTO->text)) {
                        $mainQuery->setMinScore(0);
                    } else {
                        $queryTermsAsArray = explode(' ', $searchVehicleDTO->text);
                        $mainQuery->setMinScore(2 * sqrt(count($queryTermsAsArray)));
                    }
                }
                break;
        }

        $result = $this->client->getIndex($indexName)->search($mainQuery);
        /*$this->logger->notice(json_encode($result->getQuery()->toArray()));
        $resultToArray = array_map(function (Result $r){
            return $r->getHit();
        }, $result->getResults());
        $this->logger->notice(json_encode($resultToArray));*/

        return $result;
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


    public function getVehicleInfoFilterValue(array $data): array
    {
        $aggregations = $this->getVehicleInfoAggregates();

        if (isset($data['vehicle.make.keyword'])) {
            $aggregations = array_merge($aggregations, $this->getVehicleInfoAggregates(['vehicle.make.keyword' => $data['vehicle.make.keyword']]));
            if (isset($data['vehicle.model.keyword'])) {
                $aggregations = array_merge($aggregations, $this->getVehicleInfoAggregates([
                    'vehicle.make.keyword' => $data['vehicle.make.keyword'],
                    'vehicle.model.keyword' => $data['vehicle.model.keyword'],
                ]));
            }
            if (isset($data['engine.keyword'])) {
                $aggregations = array_merge($aggregations, $this->getVehicleInfoAggregates([
                    'vehicle.make.keyword' => $data['vehicle.make.keyword'],
                    'vehicle.model.keyword' => $data['vehicle.model.keyword'],
                    'vehicle.engine.keyword' => $data['vehicle.engine.keyword'],
                ]));
            }

        }
        return $aggregations;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getVehicleInfoAggregates(array $data = []): array
    {
        $mainQuery = new Query();

        $qb = new QueryBuilder();

        $boolQuery = $qb->query()->bool();
        foreach ($data as $field => $value) {
            if (!empty($value)) {
                $boolQuery->addMust($qb->query()->term([$field => $value]));
            }
        }
        $mainQuery->setQuery($boolQuery);

        $fuelTermsAgg = $qb->aggregation()->terms("vehicle.fuel.keyword");
        $fuelTermsAgg->setField('vehicle.fuel.keyword');
        $fuelTermsAgg->setSize(1000);
        $mainQuery->addAggregation($fuelTermsAgg);


        if (empty($data)) {
            $makeTermsAgg = $qb->aggregation()->terms("vehicle.make.keyword");
            $makeTermsAgg->setField('vehicle.make.keyword');
            $makeTermsAgg->setSize(1000); // Set size to 0 is not working to not limit the size
            $mainQuery->addAggregation($makeTermsAgg);
        }

        $childAggregations = [];
        $formattedAggregations = [];
        $aggregationMapping = [
            'vehicle.make.keyword' => ['vehicle.model.keyword', 'vehicle.engine.keyword', 'vehicle.fuel.keyword'],
            'vehicle.model.keyword' => ['vehicle.engine.keyword', 'vehicle.fuel.keyword'],
            'vehicle.engine.keyword' => ['vehicle.fuel.keyword'],
            'vehicle.fuel.keyword' => ['vehicle.engine.keyword']
        ];
        foreach ($aggregationMapping as $key => $children) {
            $childAggregations = (isset($data[$key]) && !empty($data[$key]) ? $children : $childAggregations);
        }
        foreach ($childAggregations as $aggregationField) {
            $childTermsAgg = $qb->aggregation()->terms($aggregationField);
            $childTermsAgg->setField($aggregationField);
            $childTermsAgg->setSize(1000); // Set size to 0 is not working to not limit the size
            $mainQuery->addAggregation($childTermsAgg);
        }




        $resultSet = $this->client->getIndex($this->searchItemIndexer->getIndexName())->search($mainQuery);
        $this->logger->notice(json_encode($resultSet->getQuery()->toArray()));
        $resultToArray = array_map(function (Result $r){
            return $r->getHit();
        }, $resultSet->getResults());
        $this->logger->notice(json_encode($resultToArray));


        foreach ($resultSet->getAggregations() as $field => $aggregation) {
            $cleanAggregation = array_map(function ($aggregationDetail) {
                return $aggregationDetail['key'];
            }, $aggregation['buckets']);
            sort($cleanAggregation);
            $formattedAggregations[$field] = array_combine($cleanAggregation, $cleanAggregation);
        }
        return $formattedAggregations;
    }
}