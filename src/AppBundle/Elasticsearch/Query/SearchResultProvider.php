<?php


namespace AppBundle\Elasticsearch\Query;


use AppBundle\Controller\Front\ProContext\SearchController;
use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use AppBundle\Elasticsearch\Type\IndexableProVehicle;
use AppBundle\Form\DTO\SearchVehicleDTO;
use Novaway\ElasticsearchClient\Query\QueryBuilder;
use Novaway\ElasticsearchClient\Query\Result;
use Novaway\ElasticsearchClient\QueryExecutor;
use Symfony\Component\Form\FormInterface;

class SearchResultProvider
{
    const LIMIT = 9;
    const MIN_SCORE = 0.05;
    const OFFSET = 0;

    /** @var QueryExecutor */
    private $queryExecutor;

    /** @var QueryBuilderFilterer */
    private $queryBuilderFilterer;

    /** @var array */
    private $queryTypes;

    /**
     * SearchResultProvider constructor.
     * @param QueryExecutor $queryExecutor
     * @param QueryBuilderFilterer $queryBuilderFilterer
     */
    public function __construct(QueryExecutor $queryExecutor, QueryBuilderFilterer $queryBuilderFilterer)
    {
        $this->queryExecutor = $queryExecutor;
        $this->queryBuilderFilterer = $queryBuilderFilterer;
        $this->queryTypes = [SearchController::QUERY_ALL, SearchController::QUERY_RECOVERY, SearchController::QUERY_PROJECT];
    }

    /**
     * @param FormInterface $searchForm
     * @param array $pages
     * @return array
     */
    public function getSearchProResult(FormInterface $searchForm, array $pages): array
    {
        $searchVehicleDTO = $searchForm->getData();
        $formValidate = $searchForm->isSubmitted() && $searchForm->isValid();

        $searchResult = [];
        foreach ($this->queryTypes as $queryType) {
            $searchResult[$queryType] = $this->getQueryProResult($queryType, $searchVehicleDTO, $pages, $formValidate);
        }

        return $searchResult;
    }

    /**
     * @param string $queryType
     * @param SearchVehicleDTO $searchVehicleDTO
     * @param array $pages
     * @param bool $submittedAndValid
     * @return Result
     */
    private function getQueryProResult(string $queryType, SearchVehicleDTO $searchVehicleDTO, array $pages, bool $submittedAndValid): Result
    {

        $queryBuilder = QueryBuilder::createNew(
            self::OFFSET + ($pages[$queryType] - 1) * self::LIMIT,
            self::LIMIT,
            self::MIN_SCORE
        );

        if ($submittedAndValid) {
            $queryBuilder = $this->queryBuilderFilterer->getQueryProBuilder($queryBuilder, $searchVehicleDTO, $queryType);
        }

        return $this->queryExecutor->execute(
            $queryBuilder->getQueryBody(),
            IndexablePersonalVehicle::TYPE
        );
    }

    /**
     * @param $searchForm
     * @param $page
     * @return Result
     */
    public function getSearchPersonalResult(FormInterface $searchForm, int $page): Result
    {
        $searchVehicleDTO = $searchForm->getData();

        $queryBuilder = QueryBuilder::createNew(
            self::OFFSET + ($page - 1) * self::LIMIT,
            self::LIMIT,
            self::MIN_SCORE
        );

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $queryBuilder = $this->queryBuilderFilterer->getQueryPersonalBuilder($queryBuilder, $searchVehicleDTO);
        }

        return $this->queryExecutor->execute(
            $queryBuilder->getQueryBody(),
            IndexableProVehicle::TYPE
        );
    }
}
