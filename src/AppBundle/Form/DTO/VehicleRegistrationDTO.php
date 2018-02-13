<?php


namespace AppBundle\Form\DTO;


class VehicleRegistrationDTO
{

    /** @var string|null Mine type */
    private $mineType;
    /** @var string|null $plateNumber Vehicle plate number */
    private $plateNumber;
    /** @var string|null $vin Vehicle Identification Number */
    private $vin;

    /**
     * VehicleRegistrationDTO constructor.
     * @param string $mineType
     * @param string $plateNumber
     * @param string $vin
     */
    public function __construct(string $mineType = null, string $plateNumber = null, string $vin = null)
    {
        $this->mineType = $mineType;
        $this->plateNumber = $plateNumber;
        $this->vin = $vin;
    }

    /**
     * @param string|null $mineType
     * @param string|null $plateNumber
     * @param string|null $vin
     * @return VehicleRegistrationDTO
     */
    public static function buildFromVehicleRegistrationData(string $mineType = null, string $plateNumber = null, string $vin = null)
    {
        $dto = new self();
        $dto->setMineType($mineType);
        $dto->setPlateNumber($plateNumber);
        $dto->setVin($vin);
        return $dto;
    }

    /**
     * @return string
     */
    public function getMineType(): ?string
    {
        return $this->mineType;
    }

    /**
     * @param string $mineType
     * @return VehicleRegistrationDTO
     */
    public function setMineType(string $mineType = null): VehicleRegistrationDTO
    {
        $this->mineType = $mineType;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlateNumber(): ?string
    {
        return $this->plateNumber;
    }

    /**
     * @param string $plateNumber
     * @return VehicleRegistrationDTO
     */
    public function setPlateNumber(string $plateNumber = null): VehicleRegistrationDTO
    {
        $this->plateNumber = $plateNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getVin(): ?string
    {
        return $this->vin;
    }

    /**
     * @param string $vin
     * @return VehicleRegistrationDTO
     */
    public function setVin(string $vin = null): VehicleRegistrationDTO
    {
        $this->vin = $vin;
        return $this;
    }
}