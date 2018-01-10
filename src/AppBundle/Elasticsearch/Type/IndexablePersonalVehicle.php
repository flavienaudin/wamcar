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
    private $userUrl;
    /** @var string */
    private $userName;
    /** @var string */
    private $userPicture;
    /** @var string */
    private $projectBudget;
    /** @var string */
    private $projectDescription;
    /** @var array */
    private $projectVehicles;


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
                                string $transmission,
                                string $fuel,
                                string $years,
                                string $mileage,
                                string $cityName,
                                string $latitude,
                                string $longitude,
                                \DateTime $createdAt,
                                string $picture,
                                string $userUrl,
                                string $userName,
                                string $userPicture,
                                Project $userProject
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
        $this->milage = $mileage;
        $this->cityName = $cityName;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->createdAt = $createdAt;
        $this->picture = $picture;
        $this->userUrl = $userUrl;
        $this->userName = $userName;
        $this->userPicture = $userPicture;
        $this->fillUserProject($userProject);
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

    public function fillUserProject(Project $project)
    {
        $this->projectBudget = $project->getBudget();
        $this->projectDescription = $project->getDescription();

        $projectVehicles = [];
        foreach ($project->getProjectVehicles() as $projectVehicle) {
            $projectVehicles[] = [
                'make' => $projectVehicle->getMake(),
                'model' => $projectVehicle->getModel(),
                'yearMax' => $projectVehicle->getYearMax(),
                'mileageMax' => $projectVehicle->getMileageMax()
            ];
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
            'mileage' => $this->milage,
            'cityName' => $this->cityName,
            'location' => [
                'lat' => $this->latitude,
                'lon' => $this->longitude
            ],
            'createdAt' => $this->createdAt,
            'picture' => $this->picture,
            'userUrl' => $this->userUrl,
            'userName' => $this->userName,
            'userPicture' => $this->userPicture,
            'projectBudget' => $this->projectBudget,
            'projectDescription' => $this->projectDescription,
            'projectVehicles' => $this->projectVehicles
        ];
    }

}
