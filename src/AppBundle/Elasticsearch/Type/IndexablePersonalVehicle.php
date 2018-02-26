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
    /** @var string */
    private $projectBudget;
    /** @var array */
    private $projectVehicles;


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
     * @param null|Project $userProject
     */
    public function __construct(string $id,
                                string $detailUrl,
                                string $make,
                                string $model,
                                string $modelVersion = null,
                                string $engine,
                                string $transmission,
                                string $fuel,
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
                                ?Project $userProject
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
        $this->createdAt = $createdAt;
        $this->deletedAt = $deletedAt;
        $this->picture = $picture;
        $this->nbPicture = $nbPicture;
        $this->userUrl = $userUrl;
        $this->userName = $userName;
        $this->userPicture = $userPicture;
        $this->fillUserProject($userProject);
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

    public function fillUserProject(?Project $project)
    {
        $this->projectBudget = $project && $project->getBudget() ? $project->getBudget() : '';

        $projectVehicles = [];
        if ($project && $project->getProjectVehicles()) {
            foreach ($project->getProjectVehicles() as $projectVehicle) {
                $projectVehicles[] = [
                    'make' => $projectVehicle->getMake(),
                    'model' => $projectVehicle->getModel(),
                    'yearMin' => $projectVehicle->getYearMin(),
                    'mileageMax' => $projectVehicle->getMileageMax(),
                    'key_make' => $projectVehicle->getMake(),
                    'key_model' => $projectVehicle->getModel()
                ];
            }
        }

        $this->projectVehicles = $projectVehicles;
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
            'years' => $this->years,
            'mileage' => $this->mileage,
            'cityName' => $this->cityName,
            'location' => [
                'lat' => $this->latitude,
                'lon' => $this->longitude
            ],
            'createdAt' => $this->createdAt,
            'deletedAt' => $this->deletedAt,
            'picture' => $this->picture,
            'nbPicture' => $this->nbPicture,
            'userUrl' => $this->userUrl,
            'userName' => $this->userName,
            'userPicture' => $this->userPicture,
            'projectBudget' => $this->projectBudget,
            'projectVehicles' => $this->projectVehicles
        ];
    }

}
