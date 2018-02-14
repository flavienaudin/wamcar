<?php

namespace AppBundle\Form\DTO;

use Wamcar\Vehicle\{
    Engine, Enum\Transmission, Fuel, Make, Model, ModelVersion
};

class VehicleInformationDTO
{
    /** @var Make */
    public $make;
    /** @var Model */
    public $model;
    /** @var ModelVersion|null */
    public $modelVersion;
    /** @var Engine */
    public $engine;
    /** @var Transmission */
    public $transmission;
    /** @var Fuel */
    public $fuel;

    /**
     * @param array $filters
     */
    public function updateFromFilters(array $filters = []): void
    {
        $this->make = $filters['make'] ?? $this->make;
        $this->model = $filters['model'] ?? $this->model;
        $this->engine = $filters['engine'] ?? $this->engine;
        $this->fuel = $filters['fuel'] ?? $this->fuel;
    }

    /**
     * @return array
     */
    public function retrieveFilter(): array
    {
        return [
            'make' => $this->make,
            'model' => $this->model,
            'engine' => $this->engine,
            'fuel' => $this->fuel,
        ];
    }

    /**
     * @return ModelVersion
     */
    public function getModelVersion(): ?ModelVersion
    {
        return new ModelVersion(
            $this->modelVersion,
            new Model($this->model, new Make($this->make)),
            new Engine($this->engine, new Fuel($this->fuel))
        );
    }

    /**
     * @param $make
     * @param $model
     * @param $modelVersion|null
     * @param $engine
     * @param $transmission
     * @param $fuel
     * @return VehicleInformationDTO
     */
    public static function buildFromInformation($make, $model, $modelVersion = null, $engine, $transmission, $fuel)
    {
        $dto = new self();
        $dto->make = $make;
        $dto->model = $model;
        $dto->modelVersion = $modelVersion;
        $dto->engine = $engine;
        $dto->transmission = $transmission;
        $dto->fuel = $fuel;

        return $dto;
    }
}
