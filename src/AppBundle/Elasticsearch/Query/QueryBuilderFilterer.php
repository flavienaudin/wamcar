<?php


namespace AppBundle\Elasticsearch\Query;


use AppBundle\Controller\Front\ProContext\SearchController;
use AppBundle\Form\DTO\SearchVehicleDTO;
use Novaway\ElasticsearchClient\Filter\GeoDistanceFilter;
use Novaway\ElasticsearchClient\Filter\RangeFilter;
use Novaway\ElasticsearchClient\Filter\TermFilter;
use Novaway\ElasticsearchClient\Query\BoolQuery;
use Novaway\ElasticsearchClient\Query\CombiningFactor;
use Novaway\ElasticsearchClient\Query\MatchQuery;
use Novaway\ElasticsearchClient\Query\PrefixQuery;
use Novaway\ElasticsearchClient\Query\QueryBuilder;
use Novaway\ElasticsearchClient\Score\DecayFunctionScore;

class QueryBuilderFilterer
{
    const LIMIT_DISTANCE = '50';
    const OFFSET_SCORE = '1km';
    const SCALE_SCORE = '150km';


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
            $radius = self::LIMIT_DISTANCE;
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

        if ($queryType !== SearchController::TAB_PROJECT) {
            if (!empty($searchVehicleDTO->transmission)) {
                $queryBuilder->addFilter(new TermFilter('transmission', $searchVehicleDTO->transmission));
            }
            if (!empty($searchVehicleDTO->fuel)) {
                $queryBuilder->addFilter(new TermFilter('fuel', $searchVehicleDTO->fuel));
            }
        }

        $queryBuilder = $this->addSort($queryBuilder, $searchVehicleDTO);

        return $queryBuilder;
    }

    /**
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

        $queryBuilder = $this->addSort($queryBuilder, $searchVehicleDTO);

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
                $boolQuery->addClause(new MatchQuery('key_make', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_model', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_engine', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('description', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
            }
            if ($queryType === SearchController::QUERY_PROJECT || $queryType === SearchController::QUERY_ALL ||
                $queryType === SearchController::TAB_PROJECT || $queryType === SearchController::TAB_ALL
            ) {
                $boolQuery->addClause(new MatchQuery('projectDescription', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('projectVehicles.key_make', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('projectVehicles.key_model', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
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
     * @return QueryBuilder
     */
    private function addSort(QueryBuilder $queryBuilder, SearchVehicleDTO $searchVehicleDTO): QueryBuilder
    {
        if (!empty($searchVehicleDTO->cityName)) {
            $score = new DecayFunctionScore('location', DecayFunctionScore::GAUSS, ['lat' => $searchVehicleDTO->latitude, 'lon' => $searchVehicleDTO->longitude], self::OFFSET_SCORE, self::SCALE_SCORE);
            $queryBuilder->addFunctionScore($score);
        }


        if (empty($searchVehicleDTO->cityName) && empty($searchVehicleDTO->text)) {
            $queryBuilder->addSort('sortCreatedAt', 'desc');
        } else {
            $score = new DecayFunctionScore('sortCreatedAt', DecayFunctionScore::LINEAR, date('Y-m-d\TH:i:s\Z'), '1m', '9999d');
            $queryBuilder->addFunctionScore($score);
        }

        return $queryBuilder;
    }

    /**
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
            $queryBuilder->addFilter(new GeoDistanceFilter('location', $searchVehicleDTO->latitude, $searchVehicleDTO->longitude, self::LIMIT_DISTANCE));
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
        $queryBuilder = QueryBuilder::createNew(QueryBuilder::DEFAULT_OFFSET, 0);

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
