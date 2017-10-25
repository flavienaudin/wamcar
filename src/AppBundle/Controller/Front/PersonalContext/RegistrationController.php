<?php

namespace AppBundle\Controller\Front\PersonalContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Type\VehicleInfo;
use AppBundle\Form\DTO\VehicleDTO;
use AppBundle\Form\EntityBuilder\PersonalVehicleBuilder;
use AppBundle\Form\Type\VehicleType;
use Novaway\ElasticsearchClient\Aggregation\Aggregation;
use Novaway\ElasticsearchClient\Filter\TermFilter;
use Novaway\ElasticsearchClient\Query\QueryBuilder;
use Novaway\ElasticsearchClient\QueryExecutor;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\Vehicle\VehicleRepository;

class RegistrationController extends BaseController
{
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var VehicleRepository */
    private $vehicleRepository;
    /** @var QueryExecutor */
    private $queryExecutor;

    /**
     * RegistrationController constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param VehicleRepository $vehicleRepository
     * @param QueryExecutor $queryExecutor
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleRepository $vehicleRepository,
        QueryExecutor $queryExecutor
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleRepository = $vehicleRepository;
        $this->queryExecutor = $queryExecutor;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function vehicleRegistrationAction(Request $request): Response
    {
        $vehicleDTO = new VehicleDTO();
        $vehicleForm = $this->formFactory->create(
            VehicleType::class,
            $vehicleDTO,
            ['available_values' => $this->getVehicleInfoAggregates()]
        );

        $vehicleForm->handleRequest($request);
        dump($vehicleForm->get('specifics'));

        if ($vehicleForm->isSubmitted() && $vehicleForm->isValid()) {
            $personalVehicle = PersonalVehicleBuilder::buildFromDTO($vehicleDTO);
            $this->vehicleRepository->add($personalVehicle);

            dump("Picture saved");
            dump($personalVehicle);
            exit;
        }


        return $this->render(
            ':front/personalContext/registration:vehicle_registration.html.twig',
            [
                'vehicleForm' => $vehicleForm->createView(),
            ]
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function updateVehicleRegistrationFormAction(Request $request): Response
    {
        $vehicleDTO = new VehicleDTO();
        $filters = $request->get('filters', []);

        $vehicleDTO->updateFromFilters($filters);

        $vehicleForm = $this->formFactory->create(
            VehicleType::class,
            $vehicleDTO,
            ['available_values' => $this->getVehicleInfoAggregates($filters)]
        );

        return $this->render(
            ':front/personalContext/registration:vehicle_registration_form.html.twig',
            [
                'vehicleForm' => $vehicleForm->createView(),
            ]
        );
    }

    /**
     * @param array $data
     * @return array
     */
    private function getVehicleInfoAggregates(array $data = []): array
    {
        $qb = QueryBuilder::createNew(QueryBuilder::DEFAULT_OFFSET, 0);

        foreach ($data as $field => $value) {
            $qb->addFilter(new TermFilter($field, $value));
        }

        $qb->addAggregation(new Aggregation('makes', 'terms', 'make'));
        $qb->addAggregation(new Aggregation('fuels', 'terms', 'fuel'));
        if(isset($data['make'])) {
            $qb->addAggregation(new Aggregation('models', 'terms', 'model'));
        }
        if(isset($data['model'])) {
            $qb->addAggregation(new Aggregation('modelVersions', 'terms', 'engineName')); // TODO : add a version column
            $qb->addAggregation(new Aggregation('engines', 'terms', 'engineName'));
        }

        $result = $this->queryExecutor->execute($qb->getQueryBody(), VehicleInfo::TYPE);

        $formattedAggregations = [];
        foreach ($result->aggregations() as $field => $aggregation) {
            $cleanAggregation = array_map(function ($aggregationDetail) {
                return $aggregationDetail['key'];
            }, $aggregation);
            sort($cleanAggregation);
            $formattedAggregations[$field] = array_combine($cleanAggregation, $cleanAggregation);
        }

        return $formattedAggregations;
    }
}
