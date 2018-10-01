<?php

namespace AppBundle\Elasticsearch\Type;

use Novaway\ElasticsearchClient\Indexable;

class IndexableVehicleInfo implements Indexable
{
    const TYPE = 'vehicle_info';

    /** @var string tecdoc_ktypnr */
    private $ktypNumber;
    /** @var string tecdoc_constr */
    private $makeOrig;
    /** @var string TRAIT-1 */
    private $make;
    /** @var int tecdoc_constrcode */
    private $makeCode;
    /** @var string tecdoc_model1 */
    private $model;
    /** @var int tecdoc_modelcode */
    private $modelCode;
    /** @var string tecdoc_codemoteur */
    private $engineCode;
    /** @var string tecdoc_cyl */
    private $engine;
    /** @var \DateTimeInterface (tecdoc_moisdseb, tecdoc_anneedeb) */
    private $startDate;
    /** @var \DateTimeInterface|null (tecdoc_moisfin, tecdoc_anneefin) */
    private $endDate;
    /** @var float tecdoc_litr */
    private $engineSize;
    /* @var int tecdoc_ccmtech */
    private $engineCm3;
    /** @var int tecdoc_kw */
    private $kwPower;
    /** @var int tecdoc_cv */
    private $horsePower;
    /** @var string tecdoc_carross */
    private $body;
    /** @var string tecdoc_propulsion */
    private $wheelDrive;
    /** @var string tecdoc_energie */
    private $fuelOrig;
    /** @var string TRAIT-2 */
    private $fuel;
    /** @var int tecdoc_nbcyl */
    private $nbCylinders;
    /** @var int tecdoc_nbsoup */
    private $nbValve;

    /**
     * VehicleInfo constructor.
     * @param string $ktypNumber
     * @param string $makeOrig
     * @param string $make
     * @param int $makeCode
     * @param string $model
     * @param int $modelCode
     * @param string $engineCode
     * @param string $engine
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @param float $engineSize
     * @param int $engineCm3
     * @param int $kwPower
     * @param int $horsePower
     * @param string $body
     * @param string $wheelDrive
     * @param string $fuelOrig
     * @param string $fuel
     * @param int $nbCylinders
     * @param int $nbValve
     */
    public function __construct(string $ktypNumber,
                                string $makeOrig,
                                string $make,
                                int $makeCode,
                                string $model,
                                int $modelCode,
                                string $engineCode,
                                string $engine,
                                \DateTimeInterface $startDate,
                                ?\DateTimeInterface $endDate,
                                float $engineSize,
                                int $engineCm3,
                                int $kwPower,
                                int $horsePower,
                                string $body,
                                string $wheelDrive,
                                string $fuelOrig,
                                string $fuel,
                                int $nbCylinders,
                                int $nbValve
    )
    {
        $this->ktypNumber = $ktypNumber;
        $this->makeOrig = $makeOrig;
        $this->make = $make;
        $this->makeCode = $makeCode;
        $this->model = $model;
        $this->modelCode = $modelCode;
        $this->engineCode = $engineCode;
        $this->engine = $engine;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->engineSize = $engineSize;
        $this->engineCm3 = $engineCm3;
        $this->kwPower = $kwPower;
        $this->horsePower = $horsePower;
        $this->body = $body;
        $this->wheelDrive = $wheelDrive;
        $this->fuelOrig = $fuelOrig;
        $this->fuel = $fuel;
        $this->nbCylinders = $nbCylinders;
        $this->nbValve = $nbValve;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->ktypNumber;
    }

    /**
     * @return bool
     */
    public function shouldBeIndexed(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ktypNumber' => $this->ktypNumber,
            'makeOrig' => $this->makeOrig,
            'make' => $this->make,
            'makeCode' => $this->makeCode,
            'model' => $this->model,
            'modelUppercase' => strtoupper($this->model),
            'modelCode' => $this->modelCode,
            'engineCode' => $this->engineCode,
            'engine' => $this->engine,
            'engineUppercase' => strtoupper($this->engine),
            'startDate' => $this->startDate->format('Y-m-d'),
            'endDate' => $this->endDate ? $this->endDate->format('Y-m-d') : null,
            'engineSize' => $this->engineSize,
            'engineCm3' => $this->engineCm3,
            'kwPower' => $this->kwPower,
            'horsePower' => $this->horsePower,
            'body' => $this->body,
            'wheelDrive' => $this->wheelDrive,
            'fuelOrig' => $this->fuelOrig,
            'fuel' => $this->fuel,
            'nbCylinders' => $this->nbCylinders,
            'nbValve' => $this->nbValve,
        ];
    }

}
