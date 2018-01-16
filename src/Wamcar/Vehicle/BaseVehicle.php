<?php

namespace Wamcar\Vehicle;

use AppBundle\Doctrine\Entity\VehiclePicture;
use Doctrine\Common\Collections\Collection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Ramsey\Uuid\Uuid;
use Wamcar\Location\City;
use Wamcar\Vehicle\Enum\{
    MaintenanceState, SafetyTestDate, SafetyTestState, Transmission
};

abstract class BaseVehicle implements Vehicle
{
    use SoftDeleteable;

    /** @var string */
    protected $id;
    /** @var ModelVersion */
    protected $modelVersion;
    /** @var Transmission */
    protected $transmission;
    /**
     * TODO non utilisé : à supprimer ?
     * @var Registration|null
     */
    protected $registration;
    /** @var \DateTimeInterface */
    protected $registrationDate;
    /** @var int */
    protected $mileage;
    /** @var Collection */
    protected $pictures;
    /** @var SafetyTestDate */
    protected $safetyTestDate;
    /** @var SafetyTestState */
    protected $safetyTestState;
    /** @var int */
    protected $bodyState;
    /** @var int|null */
    protected $engineState;
    /** @var int|null */
    protected $tyreState;
    /** @var MaintenanceState */
    protected $maintenanceState;
    /** @var bool|null */
    protected $isTimingBeltChanged;
    /** @var bool|null */
    protected $isImported;
    /** @var bool|null */
    protected $isFirstHand;
    /** @var string|null */
    protected $additionalInformation;
    /** @var City */
    protected $city;
    /** @var \DateTimeInterface */
    protected $createdAt;
    /** @var \DateTimeInterface */
    protected $updatedAt;

    /**
     * BaseVehicle constructor.
     * @param ModelVersion $modelVersion
     * @param Transmission $transmission
     * @param Registration|null $registration
     * @param \DateTimeInterface $registrationDate
     * @param int $mileage
     * @param array $pictures
     * @param SafetyTestDate $safetyTestDate
     * @param SafetyTestState $safetyTestState
     * @param int $bodyState
     * @param int|null $engineState
     * @param int|null $tyreState
     * @param MaintenanceState $maintenanceState
     * @param bool|null $isTimingBeltChanged
     * @param bool|null $isImported
     * @param bool|null $isFirstHand
     * @param string|null $additionalInformation
     * @param City $city
     */
    public function __construct(
        ModelVersion $modelVersion,
        Transmission $transmission,
        Registration $registration = null,
        \DateTimeInterface $registrationDate,
        int $mileage,
        array $pictures,
        SafetyTestDate $safetyTestDate,
        SafetyTestState $safetyTestState,
        int $bodyState,
        int $engineState = null,
        int $tyreState = null,
        MaintenanceState $maintenanceState,
        bool $isTimingBeltChanged = null,
        bool $isImported = null,
        bool $isFirstHand = null,
        string $additionalInformation = null,
        City $city = null)
    {
        $this->id = Uuid::uuid4();
        $this->modelVersion = $modelVersion;
        $this->transmission = $transmission;
        $this->registration = $registration;
        $this->registrationDate = $registrationDate;
        $this->mileage = $mileage;
        $this->pictures = $pictures;
        $this->safetyTestDate = $safetyTestDate;
        $this->safetyTestState = $safetyTestState;
        $this->bodyState = $bodyState;
        $this->engineState = $engineState;
        $this->tyreState = $tyreState;
        $this->maintenanceState = $maintenanceState;
        $this->isTimingBeltChanged = $isTimingBeltChanged;
        $this->isImported = $isImported;
        $this->isFirstHand = $isFirstHand;
        $this->additionalInformation = $additionalInformation;
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getMake() . ' ' . $this->getModelName();
    }

    /**
     * @return string
     */
    public function getMake(): string
    {
        return $this->modelVersion->getModel()->getMake()->getName();
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return $this->modelVersion->getModel()->getName();
    }

    /**
     * @return string
     */
    public function getModelVersionName(): string
    {
        return $this->modelVersion->getName();
    }

    /**
     * @return string
     */
    public function getFuelName(): string
    {
        return $this->modelVersion->getEngine()->getFuel()->getName();
    }

    /**
     * @return string
     */
    public function getEngineName(): string
    {
        return $this->modelVersion->getEngine()->getName();
    }

    /**
     * @return string
     */
    public function getTransmission(): Transmission
    {
        return $this->transmission;
    }

    /**
     * @return null|Registration
     */
    public function getRegistration(): ?Registration
    {
        return $this->registration;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getRegistrationDate(): \DateTimeInterface
    {
        return $this->registrationDate;
    }

    /**
     * @return string
     */
    public function getYears(): string
    {
        return $this->getRegistrationDate()->format('Y');
    }

    /**
     * @return int
     */
    public function getMileage(): int
    {
        return $this->mileage;
    }

    /**
     * @return SafetyTestDate
     */
    public function getSafetyTestDate(): SafetyTestDate
    {
        return $this->safetyTestDate;
    }

    /**
     * @return SafetyTestState
     */
    public function getSafetyTestState(): SafetyTestState
    {
        return $this->safetyTestState;
    }

    /**
     * @return int
     */
    public function getBodyState(): int
    {
        return $this->bodyState;
    }

    /**
     * @return int|null
     */
    public function getEngineState(): ?int
    {
        return $this->engineState;
    }

    /**
     * @return int|null
     */
    public function getTyreState(): ?int
    {
        return $this->tyreState;
    }

    /**
     * @return MaintenanceState
     */
    public function getMaintenanceState(): MaintenanceState
    {
        return $this->maintenanceState;
    }

    /**
     * @return bool|null
     */
    public function getisTimingBeltChanged(): ?bool
    {
        return $this->isTimingBeltChanged;
    }

    /**
     * @return bool|null
     */
    public function getisImported(): ?bool
    {
        return $this->isImported;
    }

    /**
     * @return bool|null
     */
    public function getisFirstHand(): ?bool
    {
        return $this->isFirstHand;
    }

    /**
     * @return null|string
     */
    public function getAdditionalInformation(): ?string
    {
        return $this->additionalInformation;
    }

    /**
     * @return City
     */
    private function getCity(): City
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return ($this->getCity() != null ? $this->getCity()->getPostalCode() : null);
    }

    /**
     * @return string
     */
    public function getCityName(): string
    {
        return ($this->getCity() != null ? $this->getCity()->getName() : null);
    }

    /**
     * @return string
     */
    public function getLatitude(): ?string
    {
        return ($this->getCity() != null ? $this->getCity()->getLatitude() : null);
    }

    /**
     * @return string
     */
    public function getLongitude(): ?string
    {
        return ($this->getCity() != null ? $this->getCity()->getLongitude() : null);
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }


    /**
     * @return array|Picture[]
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * @return null|Picture
     */
    public function getMainPicture(): ?Picture
    {
        if (count($this->pictures) === 0) {
            return null;
        }

        return $this->pictures[0];
    }

    /**
     * @param VehiclePicture $addPicture
     */
    public function addPicture(VehiclePicture $addPicture): void
    {
        if ($addPicture->getId()) {
            /** @var VehiclePicture $picture */
            foreach ($this->pictures as $picture) {
                if ($picture->getId() === $addPicture->getId()) {
                    $picture->setFile($addPicture->getFile());
                    $picture->setFileMimeType($addPicture->getFileMimeType());
                    $picture->setFileName($addPicture->getFileName());
                    $picture->setFileOriginalName($addPicture->getFileOriginalName());
                    $picture->setFileSize($addPicture->getFileSize());
                    $picture->setCaption($addPicture->getCaption());
                    return;
                }
            }
            $this->pictures[] = $addPicture;
        }
    }

    /**
     * @param string $vehiclePictureId
     * @param null|string $caption
     */
    public function editPictureCaption(string $vehiclePictureId, ?string $caption)
    {
        if ($vehiclePictureId) {
            /** @var VehiclePicture $picture */
            foreach ($this->pictures as $picture) {
                if ($picture->getId() === $vehiclePictureId) {
                    $picture->setCaption($caption);
                }
            }
        }
    }

    /**
     * @param string $pictureId
     */
    public function removePicture(?string $pictureId): void
    {
        if ($pictureId){
            /** @var VehiclePicture $picture */
            foreach ($this->pictures as $picture) {
                if ($picture->getId() === $pictureId) {
                    $this->pictures->removeElement($picture);
                    return;
                }
            }
        }
    }

    /**
     * @param ModelVersion $modelVersion
     */
    public function setModelVersion(ModelVersion $modelVersion): void
    {
        $this->modelVersion = $modelVersion;
    }

    /**
     * @param Transmission $transmission
     */
    public function setTransmission(Transmission $transmission): void
    {
        $this->transmission = $transmission;
    }

    /**
     * @param null|Registration $registration
     */
    public function setRegistration(?Registration $registration): void
    {
        $this->registration = $registration;
    }

    /**
     * @param \DateTimeInterface $registrationDate
     */
    public function setRegistrationDate(\DateTimeInterface $registrationDate): void
    {
        $this->registrationDate = $registrationDate;
    }

    /**
     * @param int $mileage
     */
    public function setMileage(int $mileage): void
    {
        $this->mileage = $mileage;
    }

    /**
     * @param Collection $pictures
     */
    public function setPictures(Collection $pictures): void
    {
        $this->pictures = $pictures;
    }

    /**
     * @param SafetyTestDate $safetyTestDate
     */
    public function setSafetyTestDate(SafetyTestDate $safetyTestDate): void
    {
        $this->safetyTestDate = $safetyTestDate;
    }

    /**
     * @param SafetyTestState $safetyTestState
     */
    public function setSafetyTestState(SafetyTestState $safetyTestState): void
    {
        $this->safetyTestState = $safetyTestState;
    }

    /**
     * @param int $bodyState
     */
    public function setBodyState(int $bodyState): void
    {
        $this->bodyState = $bodyState;
    }

    /**
     * @param int|null $engineState
     */
    public function setEngineState(?int $engineState): void
    {
        $this->engineState = $engineState;
    }

    /**
     * @param int|null $tyreState
     */
    public function setTyreState(?int $tyreState): void
    {
        $this->tyreState = $tyreState;
    }

    /**
     * @param MaintenanceState $maintenanceState
     */
    public function setMaintenanceState(MaintenanceState $maintenanceState): void
    {
        $this->maintenanceState = $maintenanceState;
    }

    /**
     * @param bool|null $isTimingBeltChanged
     */
    public function setIsTimingBeltChanged(?bool $isTimingBeltChanged): void
    {
        $this->isTimingBeltChanged = $isTimingBeltChanged;
    }

    /**
     * @param bool|null $isImported
     */
    public function setIsImported(?bool $isImported): void
    {
        $this->isImported = $isImported;
    }

    /**
     * @param bool|null $isFirstHand
     */
    public function setIsFirstHand(?bool $isFirstHand): void
    {
        $this->isFirstHand = $isFirstHand;
    }

    /**
     * @param null|string $additionalInformation
     */
    public function setAdditionalInformation(?string $additionalInformation): void
    {
        $this->additionalInformation = $additionalInformation;
    }

    /**
     * @param City $city
     */
    public function setCity(City $city): void
    {
        $this->city = $city;
    }
}
