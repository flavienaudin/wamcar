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
    /** @var string */
    private $transmission;
    /** @var string */
    private $fuel;
    /** @var string */
    private $years;
    /** @var string */
    private $mileage;
    /** @var string */
    private $cityName;
    /** @var string */
    private $latitude;
    /** @var string */
    private $longitude;
    /** @var int */
    private $price;
    /** @var \DateTime */
    private $createdAt;
    /** @var string */
    private $picture;
    /** @var int */
    private $nbPicture;
    /** @var string */
    private $sellerUrl;
    /** @var string */
    private $sellerName;
    /** @var string */
    private $sellerPicture;
    /** @var \DateTime */
    private $deletedAt;

    /**
     * IndexableProVehicle constructor.
     * @param string $id
     * @param string $detailUrl
     * @param string $make
     * @param string $model
     * @param string $modelVersion
     * @param string $engine
     * @param string $transmission
     * @param string $fuel
     * @param string $years
     * @param string $mileage
     * @param string $cityName
     * @param string $latitude
     * @param string $longitude
     * @param int $price
     * @param \DateTime $createdAt
     * @param string $picture
     * @param int $nbPicture
     * @param string $sellerUrl
     * @param string $sellerName
     * @param string $sellerPicture
     * @param \DateTime $deletedAt
     */
    public function __construct(string $id,
                                string $detailUrl,
                                string $make,
                                string $model,
                                string $modelVersion,
                                string $engine,
                                string $transmission,
                                string $fuel,
                                string $years,
                                string $mileage,
                                string $cityName,
                                ?string $latitude,
                                ?string $longitude,
                                int $price,
                                \DateTime $createdAt,
                                string $picture,
                                int $nbPicture,
                                string $sellerUrl,
                                string $sellerName,
                                string $sellerPicture,
                                ?\DateTime $deletedAt
    )
    {
        $this->id = $id;
        $this->detailUrl = $detailUrl;
        $this->make = $make;
        $this->model = $model;
        $this->modelVersion = $modelVersion;
        $this->engine = $engine;
        $this->transmission = $transmission;
        $this->fuel = $fuel;
        $this->years = $years;
        $this->mileage = $mileage;
        $this->cityName = $cityName;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->price = $price;
        $this->createdAt = $createdAt;
        $this->picture = $picture;
        $this->nbPicture = $nbPicture;
        $this->sellerUrl = $sellerUrl;
        $this->sellerName = $sellerName;
        $this->sellerPicture = $sellerPicture;
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
        return $this->deletedAt === null;
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
            'key_make' => $this->make,
            'key_model' => $this->model,
            'key_modelVersion' => $this->modelVersion,
            'key_engine' => $this->engine,
            'key_fuel' => $this->fuel,
            'transmission' => $this->transmission,
            'fuel' => $this->fuel,
            'years' => $this->years,
            'mileage' => $this->mileage,
            'cityName' => $this->cityName,
            'proLocation' => [
                'lat' => $this->latitude,
                'lon' => $this->longitude
            ],
            'price' => $this->price,
            'createdAt' => $this->createdAt,
            'picture' => $this->picture,
            'nbPicture' => $this->nbPicture,
            'sellerUrl' => $this->sellerUrl,
            'sellerName' => $this->sellerName,
            'sellerPicture' => $this->sellerPicture,
        ];
    }

}
