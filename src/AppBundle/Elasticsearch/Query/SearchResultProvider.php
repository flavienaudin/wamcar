<?php


namespace AppBundle\Elasticsearch\Query;


use AppBundle\Controller\Front\ProContext\SearchController;
use AppBundle\Elasticsearch\Type\IndexablePersonalProject;
use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use AppBundle\Elasticsearch\Type\IndexableProUser;
use AppBundle\Elasticsearch\Type\IndexableProVehicle;
use AppBundle\Form\DTO\SearchProDTO;
use AppBundle\Form\DTO\SearchVehicleDTO;
use Novaway\ElasticsearchClient\Query\Result;
use Novaway\ElasticsearchClient\QueryExecutor;
use Symfony\Component\Form\FormInterface;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;
use Wamcar\User\BaseUser;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;


class SearchResultProvider
{
    const LIMIT = 10;
    const MIN_SCORE = 0.1;
    const OFFSET = 0;

    /** @var QueryExecutor */
    private $queryExecutor;

    /** @var QueryBuilderFilterer */
    private $queryBuilderFilterer;

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
            $queryType === SearchController::TAB_ALL ? 0 : self::MIN_SCORE
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

        $queryBody = $queryBuilder->getQueryBody();
        return $this->queryExecutor->execute($queryBody, $types);
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
            0.3
        );

        $queryBuilder = $this->queryBuilderFilterer->getGarageVehiclesQueryBuilder($queryBuilder, $garage->getId(), $text);

        $queryBody = $queryBuilder->getQueryBody();
        return $this->queryExecutor->execute($queryBody, IndexableProVehicle::TYPE);
    }

    /**
     * @param BaseUser $user
     * @param string|null $text
     * @param int $page
     * @param int|null $limit
     * @return Result
     */
    public function getQueryUserVehiclesResult(BaseUser $user, string $text = null, int $page, int $limit = self::LIMIT): Result
    {
        $queryBuilder = new QueryBuilder(
            self::OFFSET + ($page - 1) * $limit,
            $limit,
            0.3
        );
        $type = null;
        if ($user instanceof ProUser) {
            $garageIds = [];
            /** @var GarageProUser $garageMembership */
            foreach ($user->getGarageMemberships() as $garageMembership) {
                $garageIds[] = $garageMembership->getGarage()->getId();
            }
            $queryBuilder = $this->queryBuilderFilterer->getGarageVehiclesQueryBuilder($queryBuilder, $garageIds, $text);
            $type = IndexableProVehicle::TYPE;
        } elseif ($user instanceof PersonalUser) {
            $queryBuilder = $this->queryBuilderFilterer->getUserVehiclesQueryBuilder($queryBuilder, $user->getId(), $text);
            $type = IndexablePersonalVehicle::TYPE;
        }

        $queryBody = $queryBuilder->getQueryBody();
        return $this->queryExecutor->execute($queryBody, $type);
    }

    public function getQueryDirectoryProUserResult(SearchProDTO $searchProDTO, int $page = 1, int $limit = self::LIMIT): Result
    {
        $queryBuilder = new QueryBuilder(
            self::OFFSET + ($page - 1) * $limit,
            $limit,
            0.3
        );

        $queryBuilder = $this->queryBuilderFilterer->getDirectoryProUserQueryBuilder($queryBuilder, $searchProDTO);
        $queryBody = $queryBuilder->getQueryBody();

        return $this->queryExecutor->execute($queryBody, IndexableProUser::TYPE);
    }
}
