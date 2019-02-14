<?php

namespace AppBundle\Controller\Front\ProContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Elastica\CityEntityIndexer;
use AppBundle\Elasticsearch\Elastica\ElasticUtils;
use AppBundle\Elasticsearch\Elastica\ProUserEntityIndexer;
use AppBundle\Form\DTO\SearchProDTO;
use AppBundle\Form\Type\SearchProType;
use AppBundle\Services\User\UserEditionService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class DirectoryController extends BaseController
{

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var ProUserEntityIndexer */
    private $proUserEntityIndexer;
    /** @var CityEntityIndexer */
    private $cityEntityIndexer;

    /**
     * DirectoryController constructor.
     * @param FormFactoryInterface $formFactory
     * @param ProUserEntityIndexer $proUserEntityIndexer
     * @param UserEditionService $userEditionService
     * @param CityEntityIndexer $cityEntityIndexe
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        ProUserEntityIndexer $proUserEntityIndexer,
        UserEditionService $userEditionService,
        CityEntityIndexer $cityEntityIndexe
    )
    {
        $this->formFactory = $formFactory;
        $this->proUserEntityIndexer = $proUserEntityIndexer;
        $this->userEditionService = $userEditionService;
        $this->cityEntityIndexer = $cityEntityIndexe;
    }

    public function viewAction(Request $request, int $page = 1)
    {
        // Normal use is query param. Attribute $page if for legacy purpose
        $page = $request->query->get('page', $page);

        $searchProDTO = new SearchProDTO();

        // Champ libre
        if ($request->query->has('q')) {
            $searchProDTO->text = $request->query->get('q');
        }

        // Deal with ByCity action
        if (($city = $request->get('city')) !== null) {
            $idxSplit = strpos($city, '-');
            if ($idxSplit !== false) {
                $cityPostalCode = substr($city, 0, $idxSplit);
                $cityName = substr($city, $idxSplit + 1);
                $citiesResultSet = $this->cityEntityIndexer->provideForSearch($cityName);
                if ($citiesResultSet->getTotalHits() > 0) {
                    foreach ($citiesResultSet->getResults() as $result) {
                        $hit = $result->getData();
                        if ($hit['postalCode'] === $cityPostalCode) {
                            $searchProDTO->postalCode = $hit['postalCode'];
                            $searchProDTO->cityName = $hit['cityName'];
                            $searchProDTO->latitude = $hit['latitude'];
                            $searchProDTO->longitude = $hit['longitude'];
                            break;
                        }
                    }
                }
            }
        }

        $searchProForm = $this->formFactory->create(SearchProType::class, $searchProDTO, [
            'action' => $this->generateRoute('front_directory_view')
        ]);
        $searchProForm->handleRequest($request);
        $resultSet = $this->proUserEntityIndexer->getQueryDirectoryProUserResult($searchProDTO, $page);
        $proUserResult = $this->userEditionService->getUsersBySearchResult($resultSet);

        return $this->render('front/Directory/view.html.twig', [
            'searchProForm' => $searchProForm->createView(),
            'result' => $proUserResult,
            'filterData' => (array)$searchProForm->getData(),
            'page' => $page,
            'lastPage' => ElasticUtils::numberOfPages($resultSet)
        ]);
    }
}