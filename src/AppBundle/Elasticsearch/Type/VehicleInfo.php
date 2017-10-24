<?php

namespace AppBundle\Elasticsearch\Type;

use Novaway\ElasticsearchClient\Indexable;

class VehicleInfo implements Indexable
{
    const TYPE = 'vehicle_info';

    /** @var string */
    private $ktypNumber;
    /** @var string */
    private $make;
    /** @var string */
    private $model;
    /** @var string */
    private $engineCode;
    /** @var string */
    private $engineName;
    /** @var \DateTimeInterface */
    private $startDate;
    /** @var \DateTimeInterface|null */
    private $endDate;
    /** @var float */
    private $engineSize;
    /** @var int */
    private $horsePower;
    /** @var string */
    private $body;
    /** @var string */
    private $wheelDrive;
    /** @var string */
    private $fuel;
    /** @var int */
    private $nbCylinders;
    /** @var int */
    private $nbValve;

    /**
     * VehicleInfo constructor.
     * @param string $ktypNumber
     * @param string $make
     * @param string $model
     * @param string $engineCode
     * @param string $engineName
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @param float $engineSize
     * @param int $horsePower
     * @param string $body
     * @param string $wheelDrive
     * @param string $fuel
     * @param int $nbCylinders
     * @param int $nbValve
     */
    public function __construct(string $ktypNumber,
                                string $make,
                                string $model,
                                string $engineCode,
                                string $engineName,
                                \DateTimeInterface $startDate,
                                ?\DateTimeInterface $endDate,
                                float $engineSize,
                                int $horsePower,
                                string $body,
                                string $wheelDrive,
                                string $fuel,
                                int $nbCylinders,
                                int $nbValve
    )
    {
        $this->ktypNumber = $ktypNumber;
        $this->make = $make;
        $this->model = $model;
        $this->engineCode = $engineCode;
        $this->engineName = $engineName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->engineSize = $engineSize;
        $this->horsePower = $horsePower;
        $this->body = $body;
        $this->wheelDrive = $wheelDrive;
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
            'make' => $this->make,
            'model' => $this->model,
            'engineCode' => $this->engineCode,
            'engineName' => $this->engineName,
            'startDate' => $this->startDate->format('Y-m-d'),
            'endDate' => $this->endDate ? $this->endDate->format('Y-m-d') : null,
            'engineSize' => $this->engineSize,
            'horsePower' => $this->horsePower,
            'body' => $this->body,
            'wheelDrive' => $this->wheelDrive,
            'fuel' => $this->fuel,
            'nbCylinders' => $this->nbCylinders,
            'nbValve' => $this->nbValve,
        ];
    }

}
