<?php

namespace Wamcar\Vehicle;

final class ModelVersion
{
    /** @var string|null */
    private $name;
    /** @var Model */
    private $model;
    /** @var Engine */
    private $engine;

    /**
     * ModelVersion constructor.
     * @param string|null $name
     * @param Model $model
     * @param Engine $engine
     */
    public function __construct(string $name = null, Model $model, Engine $engine)
    {
        $this->name = $name;
        $this->model = $model;
        $this->engine = $engine;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return string|null
     * @deprecated
     */
    public function getName(): ?string
    {
        $computedName = '';

        if ($this->model) {
            if ($this->model->getMake()) {
                $computedName = $this->model->getMake()->getName();
            }
            $computedName .= ' ' . $this->model->getName() . ' ';
        }

        if ($this->engine) {
            $computedName .= $this->engine->getName();
            if ($this->engine->getFuel()) {
                $computedName .= ' (' . $this->engine->getFuel()->getName() . ')';
            }
        }

        return $computedName;
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
