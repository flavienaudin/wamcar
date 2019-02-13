<?php

namespace Wamcar\Vehicle;

use AppBundle\Controller\Front\ProContext\FavoriteController;
use AppBundle\Doctrine\Entity\VehiclePicture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Ramsey\Uuid\Uuid;
use Wamcar\Location\City;
use Wamcar\User\{
    BaseLikeVehicle, BaseUser, Picture as UserPicture
};
use Wamcar\Vehicle\Enum\{
    MaintenanceState, SafetyTestState, TimingBeltState, Transmission
};

abstract class BaseVehicle implements Vehicle
{
    use SoftDeleteable;
    const TYPE = '';

    /** @var string */
    protected $id;
    /** @var string */
    protected $slug;
    /** @var ModelVersion */
    protected $modelVersion;
    /** @var Transmission */
    protected $transmission;
    /** @var Registration|null */
    protected $registration;
    /** @var bool */
    protected $isUsed;
    /** @var string */
    protected $isUsedSlugValue;
    /** @var \DateTimeInterface */
    protected $registrationDate;
    /** @var int */
    protected $mileage;
    /** @var Collection */
    protected $pictures;
    /** @var \DateTimeInterface |null */
    protected $safetyTestDate;
    /** @var SafetyTestState|null */
    protected $safetyTestState;
    /** @var int|null */
    protected $bodyState;
    /** @var int|null */
    protected $engineState;
    /** @var int|null */
    protected $tyreState;
    /** @var MaintenanceState|null */
    protected $maintenanceState;
    /** @var TimingBeltState|null */
    protected $timingBeltState;
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
    /** @var Collection */
    protected $headerMessages;
    /** @var Collection */
    protected $messages;
    /** @var Collection */
    protected $likes;

    /**
     * BaseVehicle constructor.
     * @param ModelVersion $modelVersion
     * @param Transmission $transmission
     * @param Registration|null $registration
     * @param \DateTimeInterface $registrationDate
     * @param bool $isUsed
     * @param int $mileage
     * @param array $pictures
     * @param \DateTimeInterface|null $safetyTestDate
     * @param SafetyTestState|null $safetyTestState
     * @param int|null $bodyState
     * @param int|null $engineState
     * @param int|null $tyreState
     * @param MaintenanceState|null $maintenanceState
     * @param TimingBeltState|null $timingBeltState
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
        bool $isUsed,
        int $mileage,
        array $pictures,
        \DateTimeInterface $safetyTestDate = null,
        SafetyTestState $safetyTestState = null,
        int $bodyState = null,
        int $engineState = null,
        int $tyreState = null,
        MaintenanceState $maintenanceState = null,
        TimingBeltState $timingBeltState = null,
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
        $this->isUsed = $isUsed;
        $this->isUsedSlugValue = $this->isUsed ? 'occasion' : 'neuf';
        $this->mileage = $mileage;
        $this->pictures = $pictures;
        $this->safetyTestDate = $safetyTestDate;
        $this->safetyTestState = $safetyTestState;
        $this->bodyState = $bodyState;
        $this->engineState = $engineState;
        $this->tyreState = $tyreState;
        $this->maintenanceState = $maintenanceState;
        $this->timingBeltState = $timingBeltState;
        $this->isImported = $isImported;
        $this->isFirstHand = $isFirstHand;
        $this->additionalInformation = $additionalInformation;
        $this->city = $city;

        $this->likes = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param null|string $slug
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return static::TYPE;
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
     * @return string|null
     * @deprecated
     */
    public function getModelVersionName(): ?string
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
     * @return Transmission
     */
    public function getTransmission(): Transmission
    {
        return $this->transmission;
    }

    /**
     * @return null|string
     */
    public function getRegistrationMineType(): ?string
    {
        if ($this->registration) {
            return $this->registration->getMineType();
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getRegistrationPlateNumber(): ?string
    {
        if ($this->registration) {
            return $this->registration->getPlateNumber();
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getRegistrationVin(): ?string
    {
        if ($this->registration) {
            return $this->registration->getVin();
        }
        return null;
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
     * @return string
     */
    public function getStatus(): string
    {
        return $this->isUsed ? 'VEHICLE_STATUT.USED' : 'VEHICLE_STATUT.NEW';
    }

    /**
     * @return bool
     */
    public function isUsed(): bool
    {
        return $this->isUsed;
    }

    /**
     * @return null|string
     */
    public function getIsUsedSlugValue(): ?string
    {
        return $this->isUsedSlugValue;
    }

    /**
     * @return int
     */
    public function getMileage(): int
    {
        return $this->mileage;
    }

    /**
     * @return \DateTimeInterface |null
     */
    public function getSafetyTestDate(): ?\DateTimeInterface
    {
        return $this->safetyTestDate;
    }

    /**
     * @return SafetyTestState|null
     */
    public function getSafetyTestState(): ?SafetyTestState
    {
        return $this->safetyTestState;
    }

    /**
     * @return int
     */
    public function getBodyState(): ?int
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
     * @return MaintenanceState|null
     */
    public function getMaintenanceState(): ?MaintenanceState
    {
        return $this->maintenanceState;
    }

    /**
     * @return TimingBeltState|null
     */
    public function getTimingBeltState(): ?TimingBeltState
    {
        return $this->timingBeltState;
    }

    /**
     * @return bool|null
     */
    public function isImported(): ?bool
    {
        return $this->isImported;
    }

    /**
     * @return bool|null
     */
    public function getIsFirstHand(): ?bool
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
    abstract protected function getCity(): ?City;

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
     * @return null|string
     */
    public function getCityPostalCodeAndName(): ?string
    {
        $city = $this->getCity();
        return ($city != null ? $city->getPostalCode() . ' ' . $city->getName() : null);
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
     * @return \DateTimeInterface
     */
    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return array|Picture[]
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * @return int
     */
    public function getNbPictures(): int
    {
        return count($this->pictures);
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
     * @return Collection
     */
    public function getHeaderMessages(): Collection
    {
        return $this->headerMessages;
    }

    /**
     * @return Collection
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @return Collection
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    /**
     * @return array
     */
    public function getPositiveLikes(): array
    {
        $positiveLikes = array();
        /** @var BaseLikeVehicle $like */
        foreach ($this->likes as $like) {
            if ($like->getValue() > 0) {
                $positiveLikes[] = $like;
            }
        }
        return $positiveLikes;
    }

    /**
     * Get positives likes ordered by user type : All/Pro/Personal
     * @return array
     */
    public function getPositiveLikesByUserType(): array
    {
        $positiveLikes = [
            FavoriteController::FAVORITES_ALL => [],
            FavoriteController::FAVORITES_PRO => [],
            FavoriteController::FAVORITES_PERSONAL => []
        ];
        /** @var BaseLikeVehicle $like */
        foreach ($this->likes as $like) {
            if ($like->getValue() > 0) {
                $positiveLikes[FavoriteController::FAVORITES_ALL][] = $like;
                if ($like->getUser()->isPro()) {
                    $positiveLikes[FavoriteController::FAVORITES_PRO][] = $like;
                } else {
                    $positiveLikes[FavoriteController::FAVORITES_PERSONAL][] = $like;
                }
            }
        }
        return $positiveLikes;
    }

    /**
     * @return BaseLikeVehicle|null
     */
    public function getLikeOfUser(BaseUser $user): ?BaseLikeVehicle
    {
        /** @var BaseLikeVehicle $like */
        foreach ($this->likes as $like) {
            if ($like->getUser() === $user) {
                return $like;
            }
        }
        return null;
    }

    /**
     * @param BaseLikeVehicle $likeVehicle
     */
    public function addLike(BaseLikeVehicle $likeVehicle)
    {
        $this->likes->add($likeVehicle);
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
                    $picture->setPosition($addPicture->getPosition());
                    return;
                }
            }
            $this->pictures[] = $addPicture;
        }
    }

    /**
     * Removes all pictures from entity
     */
    public function clearPictures(): void
    {
        $this->pictures->clear();
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
                    return;
                }
            }
        }
    }

    /**
     * @param string $pictureId
     */
    public function removePicture(?string $pictureId): void
    {
        if ($pictureId) {
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
     * @param null|string $plateNumber
     */
    public function setRegistrationPlateNumber(?string $plateNumber): void
    {
        $this->registration->setPlateNumber($plateNumber);
    }

    /**
     * @param null|string $vin
     */
    public function setRegistrationVin(?string $vin): void
    {
        $this->registration->setVin($vin);
    }

    /**
     * @param \DateTimeInterface $registrationDate
     */
    public function setRegistrationDate(\DateTimeInterface $registrationDate): void
    {
        $this->registrationDate = $registrationDate;
    }

    /**
     * @param bool $isUsed
     */
    public function setIsUsed(bool $isUsed): void
    {
        $this->isUsed = $isUsed;
        $this->isUsedSlugValue = $this->isUsed ? 'occasion' : 'neuf';
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
     * @param \DateTimeInterface |null $safetyTestDate
     */
    public function setSafetyTestDate(?\DateTimeInterface $safetyTestDate): void
    {
        $this->safetyTestDate = $safetyTestDate;
    }

    /**
     * @param SafetyTestState|null $safetyTestState
     */
    public function setSafetyTestState(?SafetyTestState $safetyTestState): void
    {
        $this->safetyTestState = $safetyTestState;
    }

    /**
     * @param int|null $bodyState
     */
    public function setBodyState(?int $bodyState): void
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
     * @param MaintenanceState|null $maintenanceState
     */
    public function setMaintenanceState(?MaintenanceState $maintenanceState): void
    {
        $this->maintenanceState = $maintenanceState;
    }

    /**
     * @param TimingBeltState|null $timingBeltState
     */
    public function setTimingBeltState(?TimingBeltState $timingBeltState): void
    {
        $this->timingBeltState = $timingBeltState;
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

    /**
     * @return BaseUser
     */
    abstract public function getSeller();

    /**
     * @return null|string
     */
    public function getSellerName(bool $restrictedName = false): ?string
    {
        $seller = $this->getSeller();
        if (!$seller instanceof BaseUser) {
            throw new \LogicException(sprintf('Seller must be an instance of %s, %s given', BaseUser::class, get_class($seller)));
        }
        if ($restrictedName) {
            return $seller->getFirstName();
        } else {
            return $seller->getFullName();
        }
    }

    /**
     * @return null|UserPicture
     */
    public function getSellerAvatar(): ?UserPicture
    {
        $seller = $this->getSeller();
        if (!$seller instanceof BaseUser) {
            throw new \LogicException(sprintf('Seller must be an instance of %s, %s given', BaseUser::class, get_class($seller)));
        }

        return $seller->getAvatar();
    }

    /**
     * @return bool
     */
    public function isPro(): bool
    {
        return $this instanceof ProVehicle;
    }

    /**
     * @return bool
     */
    public function isPersonal(): bool
    {
        return $this instanceof PersonalVehicle;
    }

    public function __toString()
    {
        return $this->getName() . ($this->getDeletedAt() != null ? ' (suppr)' : '');
    }
}
