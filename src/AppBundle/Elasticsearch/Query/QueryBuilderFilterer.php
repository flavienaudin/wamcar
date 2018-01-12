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
use Novaway\ElasticsearchClient\Query\QueryBuilder;

class QueryBuilderFilterer
{

    /**
     * @param QueryBuilder $queryBuilder
     * @param $searchVehicleDTO
     * @param $queryType
     * @return QueryBuilder
     */
    public function getQueryProBuilder(QueryBuilder $queryBuilder, SearchVehicleDTO $searchVehicleDTO, $queryType): QueryBuilder
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

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param $queryType
     * @param $value
     * @return QueryBuilder
     */
    private function handleText(QueryBuilder $queryBuilder, $queryType, $value)
    {
        if (!empty($value)) {
            $boolQuery = new BoolQuery();
            if ($queryType === SearchController::QUERY_RECOVERY || $queryType === SearchController::QUERY_ALL) {
                $boolQuery->addClause(new MatchQuery('key_make', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_model', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_modelVersion', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_engine', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
            }
            if ($queryType === SearchController::QUERY_PROJECT || $queryType === SearchController::QUERY_ALL) {
                $boolQuery->addClause(new MatchQuery('projectVehicles.key_make', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('projectVehicles.key_model', $value, CombiningFactor::SHOULD, ['operator' => 'OR']));
            }
            $queryBuilder->addQuery($boolQuery);
        }
        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param $queryType
     * @param $value
     * @return QueryBuilder
     */
    private function handleMake(QueryBuilder $queryBuilder, $queryType, $value)
    {
        if (!empty($value)) {
            if ($queryType === SearchController::QUERY_RECOVERY) {
                $queryBuilder->addFilter(new TermFilter('make', $value));
            }
            if ($queryType === SearchController::QUERY_PROJECT) {
                $queryBuilder->addFilter(new TermFilter('projectVehicles.make', $value));
            }
            if ($queryType === SearchController::QUERY_ALL) {
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
     * @param $queryType
     * @param $value
     * @return QueryBuilder
     */
    private function handleModel(QueryBuilder $queryBuilder, $queryType, $value)
    {
        if (!empty($value)) {
            if ($queryType === SearchController::QUERY_RECOVERY) {
                $queryBuilder->addFilter(new TermFilter('model', $value));
            }
            if ($queryType === SearchController::QUERY_PROJECT) {
                $queryBuilder->addFilter(new TermFilter('projectVehicles.model', $value));
            }
            if ($queryType === SearchController::QUERY_ALL) {
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
     * @param $queryType
     * @param $value
     * @return QueryBuilder
     */
    private function handleMileage(QueryBuilder $queryBuilder, $queryType, $value)
    {
        if (!empty($value)) {
            if ($queryType === SearchController::QUERY_RECOVERY) {
                $queryBuilder->addFilter(new RangeFilter('mileage', $value, RangeFilter::LESS_THAN_OR_EQUAL_OPERATOR));
            }
            if ($queryType === SearchController::QUERY_PROJECT) {
                $queryBuilder->addFilter(new RangeFilter('projectVehicles.mileageMax', $value, RangeFilter::GREATER_THAN_OR_EQUAL_OPERATOR));
            }
            if ($queryType === SearchController::QUERY_ALL) {
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
            $queryBuilder->addFilter(new GeoDistanceFilter('location', $searchVehicleDTO->latitude, $searchVehicleDTO->longitude, '300'));
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
        if (!empty($searchVehicleDTO->transmission)) {
            $queryBuilder->addFilter(new TermFilter('transmission', $searchVehicleDTO->transmission));
        }
        if (!empty($searchVehicleDTO->fuel)) {
            $queryBuilder->addFilter(new TermFilter('fuel', $searchVehicleDTO->fuel));
        }

        return $queryBuilder;
    }

}
