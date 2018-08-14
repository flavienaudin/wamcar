<?php


namespace AppBundle\Elasticsearch\Query;


use AppBundle\Controller\Front\ProContext\SearchController;
use AppBundle\Elasticsearch\Score\FieldValueFactorScore;
use AppBundle\Form\DTO\SearchVehicleDTO;
use Novaway\ElasticsearchClient\Filter\GeoDistanceFilter;
use Novaway\ElasticsearchClient\Filter\RangeFilter;
use Novaway\ElasticsearchClient\Filter\TermFilter;
use Novaway\ElasticsearchClient\Query\BoolQuery;
use Novaway\ElasticsearchClient\Query\CombiningFactor;
use Novaway\ElasticsearchClient\Query\MatchQuery;
use Novaway\ElasticsearchClient\Query\PrefixQuery;
use Novaway\ElasticsearchClient\Score\DecayFunctionScore;
use Wamcar\Vehicle\Enum\Sorting;

class QueryBuilderFilterer
{
    const LOCATION_RADIUS_DEFAULT = '50';
    const LOCATION_DECAY_OFFSET = '10km';
    const LOCATION_DECAY_SCALE = '75km';

    const SORTING_DATE_DECAY_OFFSET = '6h';
    const SORTING_DATE_DECAY_SCALE = '15d';


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
     * @param string|null $text
     * @return QueryBuilder
     */
    public function getGarageVehiclesQueryBuilder(QueryBuilder $queryBuilder, int $garageID, string $text = null): QueryBuilder
    {
        $queryBuilder->addFilter(new TermFilter('garageId', $garageID));
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
                '0d', '7d')
        );

        // $queryBuilder->addSort('sortingDate', 'desc');
        $queryBuilder->setMinimumScore(0.3);
        return $queryBuilder;
    }

    /**
     * @deprecated after Recherche v2
     * @param QueryBuilder $queryBuilder
     * @param SearchVehicleDTO $searchVehicleDTO
     * @param string $queryType
     * @return QueryBuilder
     */
    public function getQueryProBuilder(QueryBuilder $queryBuilder, SearchVehicleDTO $searchVehicleDTO, string $queryType): QueryBuilder
    {
        $queryBuilder = $this->handleText($queryBuilder, $queryType, $searchVehicleDTO->text);

        if (!empty($searchVehicleDTO->cityName)) {
            $queryBuilder->addFilter(new GeoDistanceFilter('location', $searchVehicleDTO->latitude, $searchVehicleDTO->longitude, '300'));
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

        if ($queryType !== SearchController::QUERY_PROJECT) {
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
     * @param string $queryType
     * @param $value
     * @return QueryBuilder
     */
    private function handleText(QueryBuilder $queryBuilder, string $queryType, $value): QueryBuilder
    {
        if (!empty($value)) {
            $boolQuery = new BoolQuery();
            if ($queryType === SearchController::QUERY_RECOVERY || $queryType === SearchController::QUERY_ALL ||
                $queryType === SearchController::TAB_PRO || $queryType === SearchController::TAB_PERSONAL || $queryType === SearchController::TAB_ALL
            ) {
                $boolQuery->addClause(new MatchQuery('key_make', $value, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
                $boolQuery->addClause(new MatchQuery('key_model', $value, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
                $boolQuery->addClause(new MatchQuery('key_engine', $value, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
                $boolQuery->addClause(new MatchQuery('description', $value, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
            }
            if ($queryType === SearchController::QUERY_PROJECT || $queryType === SearchController::QUERY_ALL ||
                $queryType === SearchController::TAB_PROJECT || $queryType === SearchController::TAB_ALL
            ) {
                $boolQuery->addClause(new MatchQuery('projectDescription', $value, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
                $boolQuery->addClause(new MatchQuery('projectVehicles.key_make', $value, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
                $boolQuery->addClause(new MatchQuery('projectVehicles.key_model', $value, CombiningFactor::SHOULD, ['operator' => 'OR', 'fuzziness' => 2]));
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
            if ($queryType === SearchController::QUERY_RECOVERY || $queryType === SearchController::TAB_PRO || $queryType === SearchController::TAB_PERSONAL) {
                $queryBuilder->addFilter(new TermFilter('make', $value));
            }
            if ($queryType === SearchController::QUERY_PROJECT || $queryType === SearchController::TAB_PROJECT) {
                $queryBuilder->addFilter(new TermFilter('projectVehicles.make', $value));
            }
            if ($queryType === SearchController::QUERY_ALL || $queryType === SearchController::TAB_ALL) {
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
            if ($queryType === SearchController::QUERY_RECOVERY || $queryType === SearchController::TAB_PRO || $queryType === SearchController::TAB_PERSONAL) {
                $queryBuilder->addFilter(new TermFilter('model', $value));
            }
            if ($queryType === SearchController::QUERY_PROJECT || $queryType === SearchController::TAB_PROJECT) {
                $queryBuilder->addFilter(new TermFilter('projectVehicles.model', $value));
            }
            if ($queryType === SearchController::QUERY_ALL || $queryType === SearchController::TAB_ALL) {
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
            if ($queryType === SearchController::QUERY_RECOVERY || $queryType === SearchController::TAB_PRO || $queryType === SearchController::TAB_PERSONAL) {
                $queryBuilder->addFilter(new RangeFilter('mileage', $value, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
            }
            if ($queryType === SearchController::QUERY_PROJECT || $queryType === SearchController::TAB_PROJECT) {
                $queryBuilder->addFilter(new RangeFilter('projectVehicles.mileageMax', $value, RangeFilter::GREATER_THAN_OR_EQUAL_OPERATOR));
            }
            if ($queryType === SearchController::QUERY_ALL || $queryType === SearchController::TAB_ALL) {
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
        switch ($searchVehicleDTO->sorting) {
            case Sorting::SEARCH_SORTING_DISTANCE:
                if (!empty($searchVehicleDTO->cityName)) {
                    $score = new DecayFunctionScore('location',
                        DecayFunctionScore::GAUSS, [
                            'lat' => $searchVehicleDTO->latitude,
                            'lon' => $searchVehicleDTO->longitude],
                        self::LOCATION_DECAY_OFFSET,
                        self::LOCATION_DECAY_SCALE);
                    $queryBuilder->addFunctionScore($score);
                    break;
                }
            // default sorting by RELEVANCE below :
            case Sorting::SEARCH_SORTING_RELEVANCE:
                if ($queryType == SearchController::TAB_PRO || $queryType == SearchController::TAB_PERSONAL) {
                    $queryBuilder->addFunctionScore(new FieldValueFactorScore(
                        "nbPositiveLikes",
                        FieldValueFactorScore::LOG1P,
                        3,
                        0
                    ));
                    $queryBuilder->addFunctionScore(new FieldValueFactorScore(
                        "nbPicture",
                        FieldValueFactorScore::LOG1P,
                        1,
                        0
                    ));
                }
                if ($queryType == SearchController::TAB_PRO) {
                    $queryBuilder->addFunctionScore(new FieldValueFactorScore(
                        "googleRating",
                        FieldValueFactorScore::LOG1P,
                        2,
                        1
                    ));
                }
                if (!empty($searchVehicleDTO->cityName)) {
                    $queryBuilder->addFunctionScore(new DecayFunctionScore('location',
                        DecayFunctionScore::GAUSS, [
                            'lat' => $searchVehicleDTO->latitude,
                            'lon' => $searchVehicleDTO->longitude],
                        self::LOCATION_DECAY_OFFSET,
                        self::LOCATION_DECAY_SCALE));
                }
                $queryBuilder->addFunctionScore(new DecayFunctionScore('sortingDate',
                    DecayFunctionScore::LINEAR,
                    date('Y-m-d\TH:i:s\Z'),
                    0,
                    self::SORTING_DATE_DECAY_SCALE));

                $queryBuilder->setFunctionScoreBoostMode(QueryBuilder::SUM);

                break;
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
        }
        return $queryBuilder;
    }

    /**
     * @deprecated after Recherche v2
     * @param QueryBuilder $queryBuilder
     * @param $searchVehicleDTO
     * @return QueryBuilder
     */
    public function getQueryPersonalBuilder(QueryBuilder $queryBuilder, SearchVehicleDTO $searchVehicleDTO): QueryBuilder
    {
        if (!empty($searchVehicleDTO->text)) {
            $boolQuery = new BoolQuery();
            $boolQuery->addClause(new MatchQuery('key_make', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
            $boolQuery->addClause(new MatchQuery('key_model', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
            $boolQuery->addClause(new MatchQuery('key_modelVersion', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
            $boolQuery->addClause(new MatchQuery('key_engine', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
            $queryBuilder->addQuery($boolQuery);
        }

        if (!empty($searchVehicleDTO->cityName)) {
            $queryBuilder->addFilter(new GeoDistanceFilter('location', $searchVehicleDTO->latitude, $searchVehicleDTO->longitude, self::LOCATION_RADIUS_DEFAULT));
        }
        if ($searchVehicleDTO->make) {
            $queryBuilder->addFilter(new TermFilter('make', $searchVehicleDTO->make));
        }
        if ($searchVehicleDTO->model) {
            $queryBuilder->addFilter(new TermFilter('model', $searchVehicleDTO->model));
        }
        if ($searchVehicleDTO->mileageMax) {
            $queryBuilder->addFilter(new RangeFilter('mileage', $searchVehicleDTO->mileageMax, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
        }
        if (!empty($searchVehicleDTO->yearsMin)) {
            $queryBuilder->addFilter(new RangeFilter('years', $searchVehicleDTO->yearsMin, RangeFilter::GREATER_THAN_OR_EQUAL_OPERATOR));
        }
        if (!empty($searchVehicleDTO->yearsMax)) {
            $queryBuilder->addFilter(new RangeFilter('years', $searchVehicleDTO->yearsMax, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
        }
        if (!empty($searchVehicleDTO->budgetMin)) {
            $queryBuilder->addFilter(new RangeFilter('price', $searchVehicleDTO->budgetMin, RangeFilter::GREATER_THAN_OR_EQUAL_OPERATOR));
        }
        if (!empty($searchVehicleDTO->budgetMax)) {
            $queryBuilder->addFilter(new RangeFilter('price', $searchVehicleDTO->budgetMax, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
        }
        if (!empty($searchVehicleDTO->transmission)) {
            $queryBuilder->addFilter(new TermFilter('transmission', $searchVehicleDTO->transmission));
        }
        if (!empty($searchVehicleDTO->fuel)) {
            $queryBuilder->addFilter(new TermFilter('fuel', $searchVehicleDTO->fuel));
        }

        $queryBuilder = $this->addSort($queryBuilder, $searchVehicleDTO);

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
