<?php

namespace AppBundle\Elasticsearch\Type;

use Novaway\ElasticsearchClient\Indexable;

class IndexablePersonalVehicle implements Indexable
{
    const TYPE = 'personal_vehicle';

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
    /** @var string */
    private $mileage;
    /** @var string */
    private $cityName;
    /** @var string */
    private $latitude;
    /** @var string */
    private $longitude;
    /** @var \DateTime */
    private $createdAt;
    /** @var \DateTime */
    private $deletedAt;
    /** @var string */
    private $picture;
    /** @var int */
    private $nbPicture;
    /** @var $userId */
    private $userId;
    /** @var int */
    private $nbPositiveLikes;

    /**
     * IndexablePersonalVehicle constructor.
     * @param string $id
     * @param string $detailUrl
     * @param string $make
     * @param string $model
     * @param string $modelVersion
     * @param string $engine
     * @param string $transmission
     * @param string $fuel
     * @param string|null $description
     * @param string $years
     * @param string $mileage
     * @param string $cityName
     * @param string $latitude
     * @param string $longitude
     * @param \DateTime $createdAt
     * @param \DateTime|null $deletedAt
     * @param string $picture
     * @param int $nbPicture
     * @param int $userId
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
                                string $latitude,
                                string $longitude,
                                \DateTime $createdAt,
                                \DateTime $deletedAt = null,
                                string $picture,
                                int $nbPicture,
                                int $userId,
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
        $this->createdAt = $createdAt;
        $this->deletedAt = $deletedAt;
        $this->picture = $picture;
        $this->nbPicture = $nbPicture;
        $this->userId = $userId;
        $this->deletedAt = $deletedAt;
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
            'makeAndModel' => $this->make . " " . $this->model,
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
            'mainSortingDate' => $this->createdAt->format('Y-m-d\TH:i:s\Z'),
            'picture' => $this->picture,
            'nbPicture' => $this->nbPicture,
            'userId' => $this->userId,
            'nbPositiveLikes' => $this->nbPositiveLikes
        ];
    }

}











