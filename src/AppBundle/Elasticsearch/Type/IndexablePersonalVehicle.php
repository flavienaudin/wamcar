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
    /** @var string */
    private $modelVersion;
    /** @var string */
    private $engine;
    /** @var string */
    private $years;
    /** @var string */
    private $milage;
    /** @var string */
    private $cityName;
    /** @var string */
    private $latitude;
    /** @var string */
    private $longitude;
    /** @var \DateTime */
    private $createdAt;
    /** @var string */
    private $picture;
    /** @var string */
    private $userName;
    /** @var string */
    private $userPicture;


    /**
     * VehicleInfo constructor.
     * @param string $id
     * @param string $detailUrl
     * @param string $make
     * @param string $model
     * @param string $modelVersion
     * @param string $engine
     * @param string $picture
     * @param string $userName
     * @param string $userPicture
     */
    public function __construct(string $id,
                                string $detailUrl,
                                string $make,
                                string $model,
                                string $modelVersion,
                                string $engine,
                                string $years,
                                string $mileage,
                                string $cityName,
                                string $latitude,
                                string $longitude,
                                \DateTime $createdAt,
                                string $picture,
                                string $userName,
                                string $userPicture
    )
    {
        $this->id = $id;
        $this->detailUrl = $detailUrl;
        $this->make = $make;
        $this->model = $model;
        $this->modelVersion = $modelVersion;
        $this->engine = $engine;
        $this->years = $years;
        $this->milage = $mileage;
        $this->cityName = $cityName;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->createdAt = $createdAt;
        $this->picture = $picture;
        $this->userName = $userName;
        $this->userPicture = $userPicture;
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
        return true;
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
            'make' => $this->make,
            'model' => $this->model,
            'modelVersion' => $this->modelVersion,
            'engine' => $this->engine,
            'years' => $this->years,
            'mileage' => $this->milage,
            'cityName' => $this->cityName,
            'location' => [
                'lat' => $this->latitude,
                'lon' => $this->longitude
            ],
            'createdAt' => $this->createdAt,
            'picture' => $this->picture,
            'userName' => $this->userName,
            'userPicture' => $this->userPicture
        ];
    }

}
