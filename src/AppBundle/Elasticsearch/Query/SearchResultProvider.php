<?php


namespace AppBundle\Elasticsearch\Query;


use AppBundle\Controller\Front\ProContext\SearchController;
use AppBundle\Elasticsearch\Type\IndexablePersonalProject;
use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use AppBundle\Elasticsearch\Type\IndexableProVehicle;
use AppBundle\Form\DTO\SearchVehicleDTO;
use Novaway\ElasticsearchClient\Query\Result;
use Novaway\ElasticsearchClient\QueryExecutor;
use Symfony\Component\Form\FormInterface;
use Wamcar\Garage\Garage;


class SearchResultProvider
{
    const LIMIT = 10;
    const MIN_SCORE = 0;
    const OFFSET = 0;

    /** @var QueryExecutor */
    private $queryExecutor;

    /** @var QueryBuilderFilterer */
    private $queryBuilderFilterer;

    /** @var array */
    private $queryTypes;

    /** @var array */
    private $tabTypes;

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
        $this->tabTypes = [SearchController::TAB_ALL, SearchController::TAB_PERSONAL, SearchController::TAB_PRO, SearchController::TAB_PROJECT];
    }


    /**
     * @param FormInterface $searchForm
     * @param array $pages
     * @return array
     */
    public function getSearchResult(FormInterface $searchForm, array $pages): array
    {
        $searchVehicleDTO = $searchForm->getData();

        $searchResult = [];
        foreach ($this->tabTypes as $tabType) {
            $searchResult[$tabType] = $this->getQueryResult($tabType, $searchVehicleDTO, $pages);
        }

        return $searchResult;
    }

    /**
     * @param string $queryType
     * @param SearchVehicleDTO $searchVehicleDTO
     * @param array $pages
     * @return Result
     */
    private function getQueryResult(string $queryType, SearchVehicleDTO $searchVehicleDTO, array $pages): Result
    {
        $queryBuilder = new QueryBuilder(
            self::OFFSET + ($pages[$queryType] - 1) * self::LIMIT,
            self::LIMIT,
            self::MIN_SCORE
        );

        $queryBuilder = $this->queryBuilderFilterer->getQuerySearchBuilder($queryBuilder, $searchVehicleDTO, $queryType);

        $types = "";
        if ($queryType === SearchController::TAB_ALL) {
            $types = join(',', [IndexablePersonalVehicle::TYPE, IndexableProVehicle::TYPE, IndexablePersonalProject::TYPE]);
        } elseif ($queryType === SearchController::TAB_PERSONAL) {
            $types = IndexablePersonalVehicle::TYPE;
        } elseif ($queryType === SearchController::TAB_PRO) {
            $types = IndexableProVehicle::TYPE;
        } elseif ($queryType === SearchController::TAB_PROJECT) {
            $types = IndexablePersonalProject::TYPE;
        }
        return $this->queryExecutor->execute($queryBuilder->getQueryBody(), $types);
    }

    /**
     * @param Garage $garage
     * @param string|null $text
     * @param int $page
     * @param int|null $limit
     * @return Result
     */
    public function getQueryGarageVehiclesResult(Garage $garage, string $text = null, int $page, int $limit = self::LIMIT): Result
    {
        $queryBuilder = new QueryBuilder(
            self::OFFSET + ($page - 1) * $limit,
            $limit,
            self::MIN_SCORE
        );

        $queryBuilder = $this->queryBuilderFilterer->getGarageVehiclesQueryBuilder($queryBuilder, $garage->getId(), $text);

        $queryBody = $queryBuilder->getQueryBody();
        return $this->queryExecutor->execute($queryBody, IndexableProVehicle::TYPE);
    }

    /**
     * @param FormInterface $searchForm
     * @param array $pages
     * @return array
     */
    public function getSearchProResult(FormInterface $searchForm, array $pages): array
    {
        $searchVehicleDTO = $searchForm->getData();

        $searchResult = [];
        foreach ($this->queryTypes as $queryType) {
            $searchResult[$queryType] = $this->getQueryProResult($queryType, $searchVehicleDTO, $pages);
        }

        return $searchResult;
    }

    /**
     * @param string $queryType
     * @param SearchVehicleDTO $searchVehicleDTO
     * @param array $pages
     * @return Result
     */
    private function getQueryProResult(string $queryType, SearchVehicleDTO $searchVehicleDTO, array $pages): Result
    {

        $queryBuilder = new QueryBuilder(
            self::OFFSET + ($pages[$queryType] - 1) * self::LIMIT,
            self::LIMIT,
            0.75
        );

        $queryBuilder = $this->queryBuilderFilterer->getQueryProBuilder($queryBuilder, $searchVehicleDTO, $queryType);

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

        $queryBuilder = new QueryBuilder(
            self::OFFSET + ($page - 1) * self::LIMIT,
            self::LIMIT,
            self::MIN_SCORE
        );

        $queryBuilder = $this->queryBuilderFilterer->getQueryPersonalBuilder($queryBuilder, $searchVehicleDTO);

        return $this->queryExecutor->execute(
            $queryBuilder->getQueryBody(),
            IndexableProVehicle::TYPE
        );
    }
}
