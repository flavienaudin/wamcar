<?php

namespace AppBundle\Elasticsearch\Type;

use Novaway\ElasticsearchClient\Indexable;
use Wamcar\User\Project;

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
    /** @var string */
    private $userUrl;
    /** @var string */
    private $userName;
    /** @var string */
    private $userPicture;
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
     * @param string $userUrl
     * @param string $userName
     * @param string $userPicture
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
                                string $mileage,
                                string $cityName,
                                string $latitude,
                                string $longitude,
                                \DateTime $createdAt,
                                \DateTime $deletedAt = null,
                                string $picture,
                                int $nbPicture,
                                string $userUrl,
                                string $userName,
                                string $userPicture,
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
        $this->userUrl = $userUrl;
        $this->userName = $userName;
        $this->userPicture = $userPicture;
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
        // key_ because conflict with not indexed in vehicle_info type
        return [
            'id' => $this->id,
            'detailUrl' => $this->detailUrl,
            'key_make' => $this->make,
            'key_model' => $this->model,
            'key_modelVersion' => $this->modelVersion,
            'key_engine' => $this->engine,
            'key_fuel' => $this->fuel,
            'make' => $this->make,
            'model' => $this->model,
            'modelVersion' => $this->modelVersion,
            'engine' => $this->engine,
            'transmission' => $this->transmission,
            'fuel' => $this->fuel,
            'description' => $this->description,
            'years' => $this->years,
            'mileage' => $this->mileage,
            'cityName' => $this->cityName,
            'location' => [
                'lat' => $this->latitude,
                'lon' => $this->longitude
            ],
            'sortCreatedAt' => $this->createdAt->format('Y-m-d\TH:i:s\Z'),
            'createdAt' => $this->createdAt,
            'deletedAt' => $this->deletedAt,
            'picture' => $this->picture,
            'nbPicture' => $this->nbPicture,
            'userUrl' => $this->userUrl,
            'userName' => $this->userName,
            'userPicture' => $this->userPicture,
            'nbPositiveLikes' => $this->nbPositiveLikes
        ];
    }

}
