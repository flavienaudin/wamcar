<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\Type\SearchVehicleType;
use Novaway\ElasticsearchClient\Query\BoolQuery;
use Novaway\ElasticsearchClient\Query\CombiningFactor;
use Novaway\ElasticsearchClient\Query\MatchQuery;
use Novaway\ElasticsearchClient\Query\QueryBuilder;
use Novaway\ElasticsearchClient\QueryExecutor;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends BaseController
{
    const MIN_SCORE = 0.05;
    const OFFSET = 0;

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var QueryExecutor */
    private $queryExecutor;
    /** @var int  */
    private $limit;



    /**
     * GarageController constructor.
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        QueryExecutor $queryExecutor,
        int $limit = 10
    )
    {
        $this->formFactory = $formFactory;
        $this->queryExecutor = $queryExecutor;
        $this->limit = $limit;

    }


    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        // default page should be one, but default offset should be 0, hence the -1
        $queryBuilder = QueryBuilder::createNew();
        $boolQuery = new BoolQuery();


        $searchVehicleDTO = new SearchVehicleDTO();
        $searchForm = $this->formFactory->create(SearchVehicleType::class, $searchVehicleDTO, [
            'method' => 'GET',
        ]);


        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            if (!empty($searchVehicleDTO->text)) {
                $boolQuery->addClause(new MatchQuery('key_make', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_model', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_modelVersion', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $boolQuery->addClause(new MatchQuery('key_engine', $searchVehicleDTO->text, CombiningFactor::SHOULD, ['operator' => 'OR']));
                $queryBuilder->addQuery($boolQuery);
            }
        }

        $queryBody = $queryBuilder->getQueryBody();
        $searchResult = $this->queryExecutor->execute(
            $queryBody,
            IndexablePersonalVehicle::TYPE
        );

        $response = $request->isXmlHttpRequest() ?
            new JsonResponse([
                'result' => $searchResult->hits()
            ])
            :
            $this->render('front/Search/search.html.twig', [
                'searchForm' => $searchForm->createView(),
                'filterData' => $searchVehicleDTO,
                'result' => $searchResult
            ])
        ;

        return $response;
    }
}
