<?php


namespace AppBundle\Controller\Front\ProContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Query\CityResultProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CityController extends BaseController
{
    /** @var CityResultProvider */
    private $resultProvider;

    public function __construct(
        CityResultProvider $resultProvider
    )
    {
        $this->resultProvider = $resultProvider;
    }

    public function autocompleteAction(Request $request)
    {
        $terms = $request->get('term');

        $cities = $this->resultProvider->provideForSearch($terms);

        return new JsonResponse([ "results" => $cities->hits()]);
    }
}
