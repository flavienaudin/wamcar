<?php


namespace AppBundle\Controller\Front\ProContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Elastica\CityEntityIndexer;
use AppBundle\Elasticsearch\Formatter\CityOptionFormatter;
use Elastica\Result;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CityController extends BaseController
{
    /** @var CityEntityIndexer */
    private $cityEntityIndexer;
    /** @var CityOptionFormatter */
    private $cityOptionFormatter;

    /**
     * CityController constructor.
     * @param CityEntityIndexer $cityEntityIndexer
     * @param CityOptionFormatter $cityOptionFormatter
     *
     */
    public function __construct(CityEntityIndexer $cityEntityIndexer, CityOptionFormatter $cityOptionFormatter)
    {
        $this->cityEntityIndexer = $cityEntityIndexer;
        $this->cityOptionFormatter = $cityOptionFormatter;
    }

    public function autocompleteAction(Request $request)
    {
        $terms = $request->get('term');
        $citiesResultSet = $this->cityEntityIndexer->provideForSearch($terms);

        $formattedResult = array_map(function (Result $result) {
            $value = $this->cityOptionFormatter->formatCityOption($result->getHit()['_source']['postalCode'], $result->getHit()['_source']['cityName']);
            return [
                'id' => $result->getHit()['_source']['postalCode'],
                'cityName' => $result->getHit()['_source']['cityName'],
                'latitude' => $result->getHit()['_source']['latitude'],
                'longitude' => $result->getHit()['_source']['longitude'],
                'text' => $value,
            ];
        }, $citiesResultSet->getResults());

        return new JsonResponse(["results" => $formattedResult]);
    }
}
