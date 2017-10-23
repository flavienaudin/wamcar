<?php

namespace AppBundle\Form\DTO;

use Wamcar\Vehicle\{
    Engine, Fuel, Make, Model, ModelVersion, Enum\Transmission
};

class VehicleIdentificationDTO
{
    /** @var Make */
    public $make;
    /** @var Model */
    public $model;
    /** @var ModelVersion */
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
        $this->modelVersion = $filters['modelVersion'] ?? $this->modelVersion;
        $this->engine = $filters['engine'] ?? $this->engine;
        $this->fuel = $filters['fuel'] ?? $this->fuel;
    }

    /**
     * @return ModelVersion
     */
    public function getModelVersion(): ?ModelVersion
    {

        return $this->modelVersion ? new ModelVersion(
            $this->modelVersion,
            new Model($this->model, new Make($this->make)),
            new Engine($this->engine, new Fuel($this->fuel))
        ) : null;
    }


}
