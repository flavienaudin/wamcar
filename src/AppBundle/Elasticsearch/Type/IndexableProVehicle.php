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
    /** @var string|null */
    private $modelVersion;
    /** @var string */
    private $engine;
    /** @var string */
    private $transmission;
    /** @var string */
    private $fuel;
    /** @var string */
    private $description;
    /** @var string */
    private $years;
    /** @var int */
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
    /** @var int */
    private $garageId;
    /** @var int */
    private $sellerId;
    /** @var \DateTime */
    private $deletedAt;
    /** @var float */
    private $googleRating;
    /** @var int */
    private $nbPositiveLikes;

    /**
     * IndexableProVehicle constructor.
     * @param string $id
     * @param string $detailUrl
     * @param string $make
     * @param string $model
     * @param string|null $modelVersion
     * @param string $engine
     * @param string $transmission
     * @param string $fuel
     * @param string $description
     * @param string $years
     * @param int $mileage
     * @param string $cityName
     * @param null|string $latitude
     * @param null|string $longitude
     * @param int $price
     * @param \DateTime $createdAt
     * @param string $picture
     * @param int $nbPicture
     * @param int $garageId
     * @param int $sellerId
     * @param \DateTime|null $deletedAt
     * @param null|float $googleRating
     * @param int $nbPositiveLikes
     */
    public function __construct(string $id,
                                string $detailUrl,
                                string $make,
                                string $model,
                                string $modelVersion = null,
                                string $engine,
                                string $transmission,
                                string $fuel,
                                ?string $description,
                                string $years,
                                int $mileage,
                                string $cityName,
                                ?string $latitude,
                                ?string $longitude,
                                int $price,
                                \DateTime $createdAt,
                                string $picture,
                                int $nbPicture,
                                int $garageId,
                                int $sellerId,
                                ?\DateTime $deletedAt,
                                ?float $googleRating,
                                int $nbPositiveLikes
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
        $this->description = $description;
        $this->years = $years;
        $this->mileage = $mileage;
        $this->cityName = $cityName;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->price = $price;
        $this->createdAt = $createdAt;
        $this->picture = $picture;
        $this->nbPicture = $nbPicture;
        $this->garageId = $garageId;
        $this->sellerId = $sellerId;
        $this->deletedAt = $deletedAt;
        $this->googleRating = $googleRating;
        $this->nbPositiveLikes = $nbPositiveLikes;
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
            'makeAndModel' => $this->make . " ". $this->model,
            'modelVersion' => $this->modelVersion,
            'engine' => $this->engine,
            'fuel' => $this->fuel,
            'transmission' => $this->transmission,
            'description' => $this->description,
            'years' => $this->years,
            'mileage' => $this->mileage,
            'cityName' => $this->cityName,
            'location' => [
                'lat' => $this->latitude,
                'lon' => $this->longitude
            ],
            'price' => $this->price,
            'mainSortingPrice' => $this->price,
            'mainSortingDate' => $this->createdAt->format('Y-m-d\TH:i:s\Z'),
            'picture' => $this->picture,
            'nbPicture' => $this->nbPicture,
            'garageId' => $this->garageId,
            'sellerId' => $this->sellerId,
            'googleRating' => $this->googleRating,
            'nbPositiveLikes' => $this->nbPositiveLikes
        ];
    }

}
