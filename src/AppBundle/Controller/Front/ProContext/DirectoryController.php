<?php

namespace AppBundle\Controller\Front\ProContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Query\CityResultProvider;
use AppBundle\Elasticsearch\Query\SearchResultProvider;
use AppBundle\Form\DTO\SearchProDTO;
use AppBundle\Form\Type\SearchProType;
use AppBundle\Services\User\UserEditionService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class DirectoryController extends BaseController
{

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var SearchResultProvider */
    private $searchResultProvider;
    /** @var UserEditionService $userEditionService */
    private $userEditionService;
    /** @var CityResultProvider */
    private $cityResultProvider;

    /**
     * DirectoryController constructor.
     * @param FormFactoryInterface $formFactory
     * @param SearchResultProvider $searchResultProvider ,
     * @param UserEditionService $userEditionService
     * @param CityResultProvider $cityResultProvider
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        SearchResultProvider $searchResultProvider,
        UserEditionService $userEditionService,
        CityResultProvider $cityResultProvider
    )
    {
        $this->formFactory = $formFactory;
        $this->searchResultProvider = $searchResultProvider;
        $this->userEditionService = $userEditionService;
        $this->cityResultProvider = $cityResultProvider;
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
                $cities = $this->cityResultProvider->provideForSearch($cityName);

                if ($cities->totalHits() > 0) {
                    foreach ($cities->hits() as $hit) {
                        if ($hit['id'] === $cityPostalCode) {
                            $searchProDTO->postalCode = $hit['id'];
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
        $result = $this->searchResultProvider->getQueryDirectoryProUserResult($searchProDTO, $page);

        $proUserResult = $this->userEditionService->getUsersBySearchResult($result);

        return $this->render('front/Directory/view.html.twig', [
            'searchProForm' => $searchProForm->createView(),
            'result' => $proUserResult,
            'filterData' => (array)$searchProForm->getData(),
            'page' => $page,
            'lastPage' => $result->numberOfPages()
        ]);
    }
}