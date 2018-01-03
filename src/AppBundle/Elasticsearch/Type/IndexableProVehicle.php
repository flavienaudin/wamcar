<?php

namespace AppBundle\Elasticsearch\Type;

use Novaway\ElasticsearchClient\Indexable;

class IndexableProVehicle implements Indexable
{
    const TYPE = 'pro_vehicle';

    /** @var string */
    private $id;
    /** @var string */
    private $detailUrl;
    /** @var string */
    private $make;
    /** @var string */
    private $model;
    /** @var string */
    private $modelVersion;
    /** @var string */
    private $engine;
    /** @var \DateTime */
    private $deletedAt;

    /**
     * VehicleInfo constructor.
     * @param string $id
     * @param string $detailUrl
     * @param string $make
     * @param string $model
     * @param string $modelVersion
     * @param string $engine
     * @param \DateTime|null $deletedAt
     */
    public function __construct(string $id,
                                string $detailUrl,
                                string $make,
                                string $model,
                                string $modelVersion,
                                string $engine,
                                \DateTime $deletedAt = null
    )
    {
        $this->id = $id;
        $this->detailUrl = $detailUrl;
        $this->make = $make;
        $this->model = $model;
        $this->modelVersion = $modelVersion;
        $this->engine = $engine;
        $this->deletedAt = $deletedAt;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function shouldBeIndexed(): bool
    {
        return empty($this->deletedAt);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'detailUrl' => $this->detailUrl,
            'make' => $this->make,
            'model' => $this->model,
            'modelVersion' => $this->modelVersion,
            'engine' => $this->engine,
            'deletedAt' => $this->deletedAt,
        ];
    }

}
