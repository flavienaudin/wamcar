<?php

namespace AppBundle\Elasticsearch\Type;

use AppBundle\Utils\SearchTypeChoice;
use Novaway\ElasticsearchClient\Indexable;
use Wamcar\Location\City;
use Wamcar\User\Project;
use Wamcar\User\ProjectVehicle;
use Wamcar\Vehicle\PersonalVehicle;

class IndexableSearchItem implements Indexable
{
    const TYPE = 'search_item';

    /** @var string */
    private $itemId;
    /** @var  int */
    private $userId;

    // VEHICLE DATA
    /** @var null|string */
    private $vehicleType;
    /** @var null|\DateTime */
    private $vehicleDeletedAt;
    /** @var null|string */
    private $vehicleLatitude;
    /** @var null|string */
    private $vehicleLongitude;
    /** @var null|string */
    private $vehicleDescription;
    /** @var null|string */
    private $vehicleMake;
    /** @var null|string */
    private $vehicleModel;
    /** @var null|string */
    private $vehicleFuel;
    /** @var null|string */
    private $vehicleTransmission;
    /** @var null|string */
    private $vehicleYears;
    /** @var null|int */
    private $vehicleMileage;
    /** @var null|int */
    private $vehiclePrice;
    /** @var null|\DateTime */
    private $vehicleCreatedAt;
    /** @var null|int */
    private $vehicleNbPictures;
    /** @var null|int */
    private $vehicleNbPositiveLikes;
    /** @var null|int */
    private $vehicleGarageId;
    /** @var null|float */
    private $vehicleGarageGoogleRating;
    /** @var boolean */
    private $isVehiclePublishable;

    // PROJECT DATA
    /** @var null|\DateTime */
    private $projectDeletedAt;
    /** @var null|string */
    private $projectDescription;
    /** @var null|int */
    private $projectBudget;
    /** @var null|\DateTime */
    private $projectUpdatedAt;
    /** @var null|array */
    private $projectModels;
    /** @var null|string */
    private $userLatitude;
    /** @var null|string */
    private $userLongitude;


    /**
     * IndexableSearchItem constructor.
     * @param string $itemId
     */
    public function __construct(string $itemId, int $userId)
    {
        $this->itemId = $itemId;
        $this->userId = $userId;
    }

    /**
     * Set Vehicle
     * @param string $vehicleType
     * @param \DateTime|null $vehicleDeletedAt
     * @param string $vehicleLatitude
     * @param string $vehicleLongitude
     * @param null|string $vehicleDescription
     * @param string $vehicleMake
     * @param string $vehicleModel
     * @param string $vehicleFuel
     * @param string $vehicleTransmission
     * @param string $vehicleYears
     * @param int $vehicleMileage
     * @param int|null $vehiclePrice
     * @param \DateTime $vehicleCreatedAt
     * @param int $vehicleNbPictures
     * @param int $vehicleNbPositiveLikes
     * @param int|null $vehicleGarageId
     * @param float|null $vehicleGarageGoogleRating
     * @param bool $isVehiclePublishable
     */
    public function setVehicle(string $vehicleType, ?\DateTime $vehicleDeletedAt,
                               string $vehicleLatitude, string $vehicleLongitude,
                               ?string $vehicleDescription,
                               string $vehicleMake, string $vehicleModel, string $vehicleFuel, string $vehicleTransmission,
                               string $vehicleYears, int $vehicleMileage, ?int $vehiclePrice,
                               \DateTime $vehicleCreatedAt, int $vehicleNbPictures, int $vehicleNbPositiveLikes,
                               ?int $vehicleGarageId, ?float $vehicleGarageGoogleRating, bool $isVehiclePublishable)
    {
        $this->vehicleType = $vehicleType;
        $this->vehicleDeletedAt = $vehicleDeletedAt;
        $this->vehicleLatitude = $vehicleLatitude;
        $this->vehicleLongitude = $vehicleLongitude;
        $this->vehicleDescription = $vehicleDescription;
        $this->vehicleMake = $vehicleMake;
        $this->vehicleModel = $vehicleModel;
        $this->vehicleFuel = $vehicleFuel;
        $this->vehicleTransmission = $vehicleTransmission;
        $this->vehicleYears = $vehicleYears;
        $this->vehicleMileage = $vehicleMileage;
        $this->vehiclePrice = $vehiclePrice;
        $this->vehicleCreatedAt = $vehicleCreatedAt;
        $this->vehicleNbPictures = $vehicleNbPictures;
        $this->vehicleNbPositiveLikes = $vehicleNbPositiveLikes;
        $this->vehicleGarageId = $vehicleGarageId;
        $this->vehicleGarageGoogleRating = $vehicleGarageGoogleRating;
        $this->isVehiclePublishable = $isVehiclePublishable;
    }


    /**
     * Set project
     * @param null|Project $userProject
     */
    public function setProject(?Project $userProject)
    {
        if ($userProject != null) {
            $this->projectDescription = $userProject->getDescription();
            $this->projectBudget = $userProject->getBudget();
            $this->projectModels = [];
            /** @var ProjectVehicle $model */
            foreach ($userProject->getProjectVehicles() as $model) {
                $this->projectModels[] = [
                    'make' => $model->getMake(),
                    'model' => $model->getModel(),
                    'makeAndModel' => $model->getMake() . ' ' . $model->getModel(),
                    'yearMin' => $model->getYearMin(),
                    'mileageMax' => $model->getMileageMax()
                ];
            }
            $this->projectUpdatedAt = $userProject->getUpdatedAt();
            $this->projectDeletedAt = $userProject->getDeletedAt();
            /** @var null|City $userCity */
            if (($userCity = $userProject->getPersonalUser()->getCity()) != null) {
                $this->userLatitude = $userCity->getLatitude();
                $this->userLongitude = $userCity->getLongitude();
            }
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->itemId;
    }

    /**
     * @return bool
     */
    public function shouldBeIndexed(): bool
    {
        return (!empty($this->vehicleType) && $this->vehicleDeletedAt == null && $this->isVehiclePublishable)
            || ($this->projectDeletedAt == null && (!empty($this->projectDescription) || !empty($this->projectBudget) || !empty($this->projectModels)));
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        // Main Sorting Date
        if (empty($this->vehicleCreatedAt)) {
            $sortingDate = $this->projectUpdatedAt;
        } else {
            $sortingDate = $this->vehicleCreatedAt;
        }
        // Main Sorting Price
        if (empty($this->vehiclePrice)) {
            $sortingPrice = $this->projectBudget;
        } else {
            $sortingPrice = $this->vehiclePrice;
        }
        // Main Sorting Location
        if (empty($this->vehicleLatitude) || empty($this->vehicleLongitude)) {
            if (!empty($this->userLatitude) && !empty($this->userLongitude)) {
                $mainSortingLocation = [
                    'lat' => $this->userLatitude,
                    'lon' => $this->userLongitude
                ];
            } else {
                $mainSortingLocation = null;
            }
        } else {
            $mainSortingLocation = [
                'lat' => $this->vehicleLatitude,
                'lon' => $this->vehicleLongitude
            ];
        }

        $toArray = [
            'id' => $this->itemId,
            'userId' => $this->userId,
            'mainSortingDate' => $sortingDate->format('Y-m-d\TH:i:s\Z'),
            'mainSortingPrice' => $sortingPrice,
            'mainSortingLocation' => $mainSortingLocation
        ];
        if (!empty($this->vehicleType)) {
            $toArray['vehicle'] = [
                'type' => $this->vehicleType,
                'make' => $this->vehicleMake,
                'model' => $this->vehicleModel,
                'makeAndModel' => $this->vehicleMake . " " . $this->vehicleModel,
                'description' => $this->vehicleDescription,
                'fuel' => strtolower($this->vehicleFuel),
                'transmission' => $this->vehicleTransmission,
                'location' => [
                    'lat' => $this->vehicleLatitude,
                    'lon' => $this->vehicleLongitude
                ],
                'years' => $this->vehicleYears,
                'mileage' => $this->vehicleMileage,
                'nbPictures' => $this->vehicleNbPictures,
                'nbPositiveLikes' => $this->vehicleNbPositiveLikes,
                'price' => $this->vehiclePrice,
                'garageId' => $this->vehicleGarageId,
                'googleRating' => $this->vehicleGarageGoogleRating
            ];
            if ($this->vehicleType == PersonalVehicle::TYPE) {
                $toArray['searchType'] = SearchTypeChoice::SEARCH_PERSONAL_VEHICLE;
            } else {
                $toArray['searchType'] = SearchTypeChoice::SEARCH_PRO_VEHICLE;
            }
        }
        if (!empty($this->projectDescription) || !empty($this->projectBudget) || !empty($this->projectModels)) {
            $toArray['project'] = [
                'description' => $this->projectDescription,
                'budget' => $this->projectBudget
            ];
            if (!empty($this->userLatitude) && !empty($this->userLongitude)) {
                $toArray['project']['location'] = [
                    'lat' => $this->userLatitude,
                    'lon' => $this->userLongitude
                ];
            }
            if (count($this->projectModels) > 0) {
                $toArray['project']['models'] = $this->projectModels;
            }
            if (!isset($toArray['searchType'])) {
                $toArray['searchType'] = SearchTypeChoice::SEARCH_PERSONAL_PROJECT;
            }
        }
        return $toArray;
    }
}
