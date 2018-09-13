<?php


namespace AppBundle\Elasticsearch\Query;


use AppBundle\Controller\Front\ProContext\SearchController;
use AppBundle\Form\DTO\SearchProDTO;
use AppBundle\Form\DTO\SearchVehicleDTO;
use Novaway\ElasticsearchClient\Filter\GeoDistanceFilter;
use Novaway\ElasticsearchClient\Filter\InArrayFilter;
use Novaway\ElasticsearchClient\Filter\RangeFilter;
use Novaway\ElasticsearchClient\Filter\TermFilter;
use Novaway\ElasticsearchClient\Query\BoolQuery;
use Novaway\ElasticsearchClient\Query\BoostMode;
use Novaway\ElasticsearchClient\Query\CombiningFactor;
use Novaway\ElasticsearchClient\Query\MatchQuery;
use Novaway\ElasticsearchClient\Query\PrefixQuery;
use Novaway\ElasticsearchClient\Score\DecayFunctionScore;
use Novaway\ElasticsearchClient\Score\FieldValueFactorScore;
use Wamcar\Vehicle\Enum\DirectorySorting;
use Wamcar\Vehicle\Enum\Sorting;

class QueryBuilderFilterer
{
    const LOCATION_RADIUS_DEFAULT = '50';
    const LOCATION_DECAY_OFFSET = '10km';
    const LOCATION_DECAY_SCALE = '75km';

    const SORTING_DATE_DECAY_OFFSET = '1d';
    const SORTING_DATE_DECAY_SCALE = '100d';
    const SORTING_DATE_DECAY_DECAY = 0.3;


    /**
     * @param QueryBuilder $queryBuilder
     * @param SearchVehicleDTO $searchVehicleDTO
     * @param string $queryType
     * @return QueryBuilder
     */
    public function getQuerySearchBuilder(QueryBuilder $queryBuilder, SearchVehicleDTO $searchVehicleDTO, string $queryType): QueryBuilder
    {
        $queryBuilder = $this->handleText($queryBuilder, $queryType, $searchVehicleDTO->text);

        if (!empty($searchVehicleDTO->cityName)) {
            $radius = self::LOCATION_RADIUS_DEFAULT;
            if (!empty($searchVehicleDTO->radius)) {
                $radius = $searchVehicleDTO->radius;
            }
            $queryBuilder->addFilter(new GeoDistanceFilter('location', $searchVehicleDTO->latitude, $searchVehicleDTO->longitude, $radius));
        }

        $queryBuilder = $this->handleMake($queryBuilder, $queryType, $searchVehicleDTO->make);
        $queryBuilder = $this->handleModel($queryBuilder, $queryType, $searchVehicleDTO->model);
        $queryBuilder = $this->handleMileage($queryBuilder, $queryType, $searchVehicleDTO->mileageMax);

        if (!empty($searchVehicleDTO->yearsMin)) {
            $queryBuilder->addFilter(new RangeFilter('years', $searchVehicleDTO->yearsMin, RangeFilter::GREATER_THAN_OR_EQUAL_OPERATOR));
        }
        if (!empty($searchVehicleDTO->yearsMax)) {
            $queryBuilder->addFilter(new RangeFilter('years', $searchVehicleDTO->yearsMax, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
        }
        if (!empty($searchVehicleDTO->budgetMin)) {
            $queryBuilder->addFilter(new RangeFilter('sortingPrice', $searchVehicleDTO->budgetMin, RangeFilter::GREATER_THAN_OR_EQUAL_OPERATOR));
        }
        if (!empty($searchVehicleDTO->budgetMax)) {
            $queryBuilder->addFilter(new RangeFilter('sortingPrice', $searchVehicleDTO->budgetMax, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
        }

        if ($queryType !== SearchController::TAB_PROJECT) {
            if (!empty($searchVehicleDTO->transmission)) {
                $queryBuilder->addFilter(new TermFilter('transmission', $searchVehicleDTO->transmission));
            }
            if (!empty($searchVehicleDTO->fuel)) {
                $queryBuilder->addFilter(new TermFilter('fuel', $searchVehicleDTO->fuel));
            }
        }

        $queryBuilder = $this->addSort($queryBuilder, $searchVehicleDTO, $queryType);

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array|int $garageIds Array of garages'id Or a garage's id
     * @param string|null $text
     * @return QueryBuilder
     */
    public function getGarageVehiclesQueryBuilder(QueryBuilder $queryBuilder, $garageIds, string $text = null): QueryBuilder
    {
        if (is_array($garageIds)) {
            $queryBuilder->addFilter(new InArrayFilter('garageId', $garageIds));
        } else {
            $queryBuilder->addFilter(new TermFilter('garageId', $garageIds));
        }
        if (!empty($text)) {
            $boolQuery = new BoolQuery();
            $boolQuery->addClause(new MatchQuery('key_make', $text, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
            $boolQuery->addClause(new MatchQuery('key_model', $text, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
            $boolQuery->addClause(new MatchQuery('key_engine', $text, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
            $boolQuery->addClause(new MatchQuery('description', $text, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
            $queryBuilder->addQuery($boolQuery);
        }
        $queryBuilder->addFunctionScore(new DecayFunctionScore('sortingDate',
                DecayFunctionScore::EXP,
                'now',
                self::SORTING_DATE_DECAY_OFFSET,
                self::SORTING_DATE_DECAY_SCALE,
                [],
                self::SORTING_DATE_DECAY_DECAY)
        );
        $queryBuilder->setBoostMode(QueryBuilder::SUM);

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param int $userId
     * @param string|null $text
     * @return QueryBuilder
     */
    public function getUserVehiclesQueryBuilder(QueryBuilder $queryBuilder, int $userId, string $text = null): QueryBuilder
    {
        $queryBuilder->addFilter(new TermFilter('userId', $userId));
        if (!empty($text)) {
            $boolQuery = new BoolQuery();
            $boolQuery->addClause(new MatchQuery('key_make', $text, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
            $boolQuery->addClause(new MatchQuery('key_model', $text, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
            $boolQuery->addClause(new MatchQuery('key_engine', $text, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
            $boolQuery->addClause(new MatchQuery('description', $text, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
            $queryBuilder->addQuery($boolQuery);
        }
        $queryBuilder->addFunctionScore(new DecayFunctionScore('sortingDate',
                DecayFunctionScore::LINEAR,
                date('Y-m-d\TH:i:s\Z'),
                self::SORTING_DATE_DECAY_OFFSET,
                self::SORTING_DATE_DECAY_SCALE,
                [],
                self::SORTING_DATE_DECAY_DECAY)
        );
        return $queryBuilder;
    }

    public function getDirectoryProUserQueryBuilder(QueryBuilder $queryBuilder, SearchProDTO $searchProDTO): QueryBuilder
    {
        if (!empty($searchProDTO->text)) {
            $boolQuery = new \AppBundle\Elasticsearch\Query\BoolQuery(1);
            $boolQuery->addClause(new MatchQuery('firstName', $searchProDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR', 'boost' => 10, 'fuzziness' => 1]));
            $boolQuery->addClause(new MatchQuery('lastName', $searchProDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR', 'boost' => 10, 'fuzziness' => 1]));
            $boolQuery->addClause(new MatchQuery('description', $searchProDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));

            $boolQuery->addClause(new MatchQuery('garages.garageName', $searchProDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR', 'boost' => 10, 'fuzziness' => 1]));
            $boolQuery->addClause(new MatchQuery('garages.garagePresentation', $searchProDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));

            $queryBuilder->addQuery($boolQuery);
        }

        if (!empty($searchProDTO->cityName)) {
            $radius = self::LOCATION_RADIUS_DEFAULT;
            if (!empty($searchProDTO->radius)) {
                $radius = $searchProDTO->radius;
            }
            $queryBuilder->addFilter(new GeoDistanceFilter('garages.garageLocation', $searchProDTO->latitude, $searchProDTO->longitude, $radius));
        }

        // sorting
        $locationOffset = self::LOCATION_DECAY_OFFSET;
        if (!empty($searchProDTO->radius)) {
            $locationOffset = ($searchProDTO->radius / 2) . 'km';
        }
        switch ($searchProDTO->sorting) {
            case DirectorySorting::DIRECTORY_SORTING_DISTANCE:
                if (!empty($searchProDTO->cityName)) {
                    $score = new DecayFunctionScore('garages.garageLocation',
                        DecayFunctionScore::GAUSS, [
                            'lat' => $searchProDTO->latitude,
                            'lon' => $searchProDTO->longitude],
                        $locationOffset,
                        self::LOCATION_DECAY_SCALE, [
                            'weight' => 10
                        ]);
                    $queryBuilder->addFunctionScore($score);
                    break;
                }
            // default sorting by RELEVANCE below :
            case DirectorySorting::DIRECTORY_SORTING_RELEVANCE:
            default:
                // Importance : 2
                $queryBuilder->addFunctionScore(new FieldValueFactorScore(
                    "maxGaragesGoogleRating",
                    FieldValueFactorScore::NONE,
                    1,
                    1
                ));

                if (!empty($searchProDTO->cityName)) {
                    // Importance : 1
                    $queryBuilder->addFunctionScore(new DecayFunctionScore('location',
                        DecayFunctionScore::GAUSS, [
                            'lat' => $searchProDTO->latitude,
                            'lon' => $searchProDTO->longitude],
                        $locationOffset,
                        self::LOCATION_DECAY_SCALE,
                        ['weight' => 2] // Decay Function score € [0;1] x 3 => [0;3]*/
                    ));
                }

                // Importance : 5
                $queryBuilder->addFunctionScore(new FieldValueFactorScore(
                    "_score",
                    FieldValueFactorScore::SQUARE,
                    1.5,
                    1
                ));
                // Functions score combination
                $queryBuilder->setFunctionScoreMode(QueryBuilder::SUM);
                // Query score and function score combination
                $queryBuilder->setBoostMode(BoostMode::REPLACE);
                break;
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $queryType
     * @param $value
     * @return QueryBuilder
     */
    private function handleText(QueryBuilder $queryBuilder, string $queryType, $value): QueryBuilder
    {
        if (!empty($value)) {
            $boolQuery = new \AppBundle\Elasticsearch\Query\BoolQuery(1);
            if ($queryType === SearchController::TAB_PRO || $queryType === SearchController::TAB_PERSONAL || $queryType === SearchController::TAB_ALL
            ) {
                $boolQuery->addClause(new MatchQuery('key_make', $value, CombiningFactor::SHOULD, ['operator' => 'OR', 'boost' => 10, 'fuzziness' => 1]));
                $boolQuery->addClause(new MatchQuery('key_model', $value, CombiningFactor::SHOULD, ['operator' => 'OR', 'boost' => 10, 'fuzziness' => 1]));
                $boolQuery->addClause(new MatchQuery('key_engine', $value, CombiningFactor::SHOULD, ['operator' => 'OR', 'boost' => 10, 'fuzziness' => 1]));
                $boolQuery->addClause(new MatchQuery('description', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
            }
            if ($queryType === SearchController::TAB_PROJECT || $queryType === SearchController::TAB_ALL
            ) {
                $boolQuery->addClause(new MatchQuery('projectDescription', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('projectVehicles.key_make', $value, CombiningFactor::SHOULD, ['operator' => 'OR', 'boost' => 10, 'fuzziness' => 1]));
                $boolQuery->addClause(new MatchQuery('projectVehicles.key_model', $value, CombiningFactor::SHOULD, ['operator' => 'OR', 'boost' => 10, 'fuzziness' => 1]));
            }
            $queryBuilder->addQuery($boolQuery);
        }
        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $queryType
     * @param $value
     * @return QueryBuilder
     */
    private function handleMake(QueryBuilder $queryBuilder, string $queryType, $value): QueryBuilder
    {
        if (!empty($value)) {
            if ($queryType === SearchController::TAB_PRO || $queryType === SearchController::TAB_PERSONAL) {
                $queryBuilder->addFilter(new TermFilter('make', $value));
            }
            if ($queryType === SearchController::TAB_PROJECT) {
                $queryBuilder->addFilter(new TermFilter('projectVehicles.make', $value));
            }
            if ($queryType === SearchController::TAB_ALL) {
                $boolQueryMake = new BoolQuery();
                $boolQueryMake->addClause(new MatchQuery('make', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQueryMake->addClause(new MatchQuery('projectVehicles.make', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $queryBuilder->addQuery($boolQueryMake);
            }
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $queryType
     * @param $value
     * @return QueryBuilder
     */
    private function handleModel(QueryBuilder $queryBuilder, string $queryType, $value): QueryBuilder
    {
        if (!empty($value)) {
            if ($queryType === SearchController::TAB_PRO || $queryType === SearchController::TAB_PERSONAL) {
                $queryBuilder->addFilter(new TermFilter('model', $value));
            }
            if ($queryType === SearchController::TAB_PROJECT) {
                $queryBuilder->addFilter(new TermFilter('projectVehicles.model', $value));
            }
            if ($queryType === SearchController::TAB_ALL) {
                $boolQueryModel = new BoolQuery();
                $boolQueryModel->addClause(new MatchQuery('model', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQueryModel->addClause(new MatchQuery('projectVehicles.model', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $queryBuilder->addQuery($boolQueryModel);
            }
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $queryType
     * @param $value
     * @return QueryBuilder
     */
    private function handleMileage(QueryBuilder $queryBuilder, string $queryType, $value): QueryBuilder
    {
        if (!empty($value)) {
            if ($queryType === SearchController::TAB_PRO || $queryType === SearchController::TAB_PERSONAL) {
                $queryBuilder->addFilter(new RangeFilter('mileage', $value, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
            }
            if ($queryType === SearchController::TAB_PROJECT) {
                $queryBuilder->addFilter(new RangeFilter('projectVehicles.mileageMax', $value, RangeFilter::GREATER_THAN_OR_EQUAL_OPERATOR));
            }
            if ($queryType === SearchController::TAB_ALL) {
                $boolQueryMileage = new BoolQuery();
                $boolQueryMileage->addClause(new RangeFilter('mileage', $value, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
                $boolQueryMileage->addClause(new RangeFilter('projectVehicles.mileageMax', $value, RangeFilter::GREATER_THAN_OR_EQUAL_OPERATOR));
                $queryBuilder->addQuery($boolQueryMileage);
            }
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param SearchVehicleDTO $searchVehicleDTO
     * @param string|null $queryType
     * @return QueryBuilder
     */
    private function addSort(QueryBuilder $queryBuilder, SearchVehicleDTO $searchVehicleDTO, string $queryType = null): QueryBuilder
    {
        $locationOffset = self::LOCATION_DECAY_OFFSET;
        if (!empty($searchVehicleDTO->radius)) {
            $locationOffset = ($searchVehicleDTO->radius / 2) . 'km';
        }
        switch ($searchVehicleDTO->sorting) {
            case Sorting::SEARCH_SORTING_DATE:
                $queryBuilder->addSort('sortingDate', 'desc');
                break;
            case Sorting::SEARCH_SORTING_PRICE_ASC:
                $queryBuilder->addSort('sortingPrice', 'asc');
                $queryBuilder->addSort('sortingDate', 'desc');
                break;
            case Sorting::SEARCH_SORTING_PRICE_DESC:
                $queryBuilder->addSort('sortingPrice', 'desc');
                $queryBuilder->addSort('sortingDate', 'desc');
                break;
            case Sorting::SEARCH_SORTING_DISTANCE:
                if (!empty($searchVehicleDTO->cityName)) {
                    $score = new DecayFunctionScore('location',
                        DecayFunctionScore::GAUSS, [
                            'lat' => $searchVehicleDTO->latitude,
                            'lon' => $searchVehicleDTO->longitude],
                        $locationOffset,
                        self::LOCATION_DECAY_SCALE, [
                            'weight' => 10
                        ]);
                    $queryBuilder->addFunctionScore($score);
                    break;
                }
            // default sorting by RELEVANCE below :
            case Sorting::SEARCH_SORTING_RELEVANCE:
            default:
                if ($queryType == SearchController::TAB_PRO || $queryType == SearchController::TAB_PERSONAL) {
                    // Importance : 3
                    $queryBuilder->addFunctionScore(new FieldValueFactorScore(
                        "nbPositiveLikes",
                        FieldValueFactorScore::LOG2P,
                        1.5,
                        0
                    ));
                    // Importance : 1
                    $queryBuilder->addFunctionScore(new FieldValueFactorScore(
                        "nbPicture",
                        FieldValueFactorScore::LOG2P,
                        0.75,
                        0
                    ));
                }
                if ($queryType == SearchController::TAB_PRO) {
                    // Importance : 2
                    $queryBuilder->addFunctionScore(new FieldValueFactorScore(
                        "googleRating",
                        FieldValueFactorScore::LOG2P,
                        1.25,
                        1
                    ));
                }
                if (!empty($searchVehicleDTO->cityName)) {
                    // Importance : 1
                    $queryBuilder->addFunctionScore(new DecayFunctionScore('location',
                        DecayFunctionScore::GAUSS, [
                            'lat' => $searchVehicleDTO->latitude,
                            'lon' => $searchVehicleDTO->longitude],
                        $locationOffset,
                        self::LOCATION_DECAY_SCALE,
                        ['weight' => 3] // Decay Function score € [0;1] x 3 => [0;3]*/
                    ));
                }

                $queryBuilder->addSort('_score', 'desc');
                $queryBuilder->addSort('sortingDate', 'desc');


                /*
                if ($queryBuilder->getFunctionScoreCollectionLength() == 0) {
                    $queryBuilder->addSort('_score', 'desc');
                    $queryBuilder->addSort('sortingDate', 'desc');
                } else {
                    // Importance : 5
                    $queryBuilder->addFunctionScore(new FieldValueFactorScore(
                        "_score",
                        FieldValueFactorScore::SQUARE,
                        5,
                        1
                    ));

                    // Importance : 2
                    $sortingDateWeight = 1.25;
                    if (empty($searchVehicleDTO->text)) {
                        $sortingDateWeight = 2;
                    }
                    $queryBuilder->addFunctionScore(new DecayFunctionScore('sortingDate',
                        DecayFunctionScore::GAUSS,
                        'now',
                        self::SORTING_DATE_DECAY_OFFSET,
                        self::SORTING_DATE_DECAY_SCALE,
                        ['weight' => $sortingDateWeight], // Decay Function score € [0;1] x1.25/x3 => [0;1.25/3]
                        self::SORTING_DATE_DECAY_DECAY
                    ));
                }*/

                // Functions score combination
                $queryBuilder->setFunctionScoreMode(QueryBuilder::SUM);
                // Query score and function score combination
                $queryBuilder->setBoostMode(BoostMode::SUM);


                break;
        }
        return $queryBuilder;
    }

    /**
     * @param string $terms
     * @return QueryBuilder
     */
    public function getQueryCity(string $terms): QueryBuilder
    {
        $queryBuilder = new QueryBuilder();

        $termsAsArray = explode(' ', $terms);

        foreach ($termsAsArray as $term) {
            $boolQuery = new BoolQuery();
            // prefix field must be not_analyzed, but the data is saved as uppercase : so we must search as uppercase
            $boolQuery->addClause(new PrefixQuery('cityName', strtoupper($term), CombiningFactor::SHOULD));
            $boolQuery->addClause(new PrefixQuery('postalCode', $term, CombiningFactor::SHOULD));
            $queryBuilder->addQuery($boolQuery);
        }

        return $queryBuilder;
    }

    /**
     * @param array $data
     * @return QueryBuilder
     */
    public function getQueryVehicleInfo(array $data): QueryBuilder
    {
        $queryBuilder = new QueryBuilder(QueryBuilder::DEFAULT_OFFSET, 0);

        foreach ($data as $field => $value) {
            if (!empty($value)) {
                $queryBuilder->addFilter(new TermFilter($field, $value));
            }
        }

        return $queryBuilder;
    }


    /**
     * @param string $ktypNumber
     * @return QueryBuilder
     */
    public function getQueryVehicleInfoByKtypNumber(string $ktypNumber): QueryBuilder
    {
        $queryBuilder = new QueryBuilder();

        $boolQuery = new BoolQuery();
        $boolQuery->addClause(new MatchQuery('ktypNumber', $ktypNumber, CombiningFactor::MUST));
        $queryBuilder->addQuery($boolQuery);

        return $queryBuilder;
    }
}
