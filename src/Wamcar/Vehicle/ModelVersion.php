<?php

namespace Wamcar\Vehicle;

final class ModelVersion
{
    /** @var string */
    private $name;
    /** @var Model */
    private $model;
    /** @var Engine */
    private $engine;

    /**
     * ModelVersion constructor.
     * @param string $name
     * @param Model $model
     * @param Engine $engine
     */
    public function __construct(string $name, Model $model, Engine $engine)
    {
        $this->name = $name;
        $this->model = $model;
        $this->engine = $engine;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @return Engine
     */
    public function getEngine(): Engine
    {
        return $this->engine;
    }
}
