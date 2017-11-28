<?php

namespace Wamcar\Vehicle;

class PersonalVehicle extends BaseVehicle
{
    /**
     * @return string
     */
    public function getMake(): string
    {
        return $this->modelVersion->getModel()->getMake()->getName();
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return $this->modelVersion->getModel()->getName();
    }

    /**
     * @return string
     */
    public function getModelVersionName(): string
    {
        return $this->modelVersion->getName();
    }

    /**
     * @return string
     */
    public function getEngineName(): string
    {
        return $this->modelVersion->getEngine()->getName();
    }
}
