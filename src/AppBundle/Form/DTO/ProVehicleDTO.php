<?php

namespace AppBundle\Form\DTO;

use AppBundle\Services\Vehicle\CanBeProVehicle;
use Wamcar\Vehicle\ProVehicle;

final class ProVehicleDTO extends VehicleDTO implements CanBeProVehicle
{
    /** @var VehicleOfferDTO */
    public $offer;

    /**
     * ProVehicleDTO constructor.
     * @param string|null $registrationNumber
     */
    public function __construct(string $registrationNumber = null)
    {
        parent::__construct($registrationNumber);
        $this->offer = new VehicleOfferDTO();
    }

    /**
     * @param ProVehicle $vehicle
     * @return ProVehicleDTO
     */
    public static function buildFromProVehicle(ProVehicle $vehicle): self
    {
        $dto = new self();
        $dto->information = VehicleInformationDTO::buildFromInformation(
            $vehicle->getMake(),
            $vehicle->getModelName(),
            $vehicle->getModelVersionName(),
            $vehicle->getEngineName(),
            $vehicle->getTransmission(),
            $vehicle->getFuelName()
        );

        $dto->specifics = VehicleSpecificsDTO::buildFromSpecifics(
            $vehicle->getRegistrationDate(),
            $vehicle->getMileage(),
            $vehicle->getisTimingBeltChanged(),
            $vehicle->getSafetyTestDate(),
            $vehicle->getSafetyTestState(),
            $vehicle->getBodyState(),
            $vehicle->getEngineState(),
            $vehicle->getTyreState(),
            $vehicle->getMaintenanceState(),
            $vehicle->getisImported(),
            $vehicle->getisFirstHand(),
            $vehicle->getAdditionalInformation(),
            $vehicle->getPostalCode(),
            $vehicle->getCityName(),
            $vehicle->getLatitude(),
            $vehicle->getLongitude()
        );

        $dto->offer = VehicleOfferDTO::buildFromOffer(
            $vehicle->getPrice(),
            $vehicle->getCatalogPrice(),
            $vehicle->getDiscount(),
            $vehicle->getGuarantee(),
            $vehicle->getOtherGuarantee(),
            $vehicle->getFunding(),
            $vehicle->getOtherFunding(),
            $vehicle->getAdditionalServices(),
            $vehicle->getReference()
        );

        $dto->pictures = [];
        foreach ($vehicle->getPictures() as $picture) {
            $dto->pictures[] = VehiclePictureDTO::buildFromPicture($picture);
        }

        $dto->pictures = self::initFormPictureVehicle($dto->pictures);

        return $dto;
    }

    /**
     * @return float
     */
    public function getPrice(): ?float
    {
        return $this->offer->price;
    }

    /**
     * @return null|float
     */
    public function getCatalogPrice(): ?float
    {
        return $this->offer->catalogPrice;
    }

    /**
     * @return null|float
     */
    public function getDiscount(): ?float
    {
        return $this->offer->discount;
    }

    /**
     * @return string
     */
    public function getGuarantee(): ?string
    {
        return $this->offer->guarantee;
    }
    /**
     * @return string
     */
    public function getOtherGuarantee(): ?string
    {
        return $this->offer->otherGuarantee;
    }

    /**
     * @return string
     */
    public function getFunding(): ?string
    {
        return $this->offer->funding;
    }
    /**
     * @return string
     */
    public function getOtherFunding(): ?string
    {
        return $this->offer->otherFunding;
    }

    /**
     * @return string
     */
    public function getAdditionalServices(): ?string
    {
        return $this->offer->additionalServices;
    }

    /**
     * @return string
     */
    public function getReference(): ?string
    {
        return $this->offer->reference;
    }

}
