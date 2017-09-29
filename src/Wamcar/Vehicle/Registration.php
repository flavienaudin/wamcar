<?php

namespace Wamcar\Vehicle;

final class Registration
{
    /** @var string */
    private $mineType;
    /** @var string */
    private $plateNumber;

    /**
     * Registration constructor.
     */
    private function __construct(string $mineType = null, string $plateNumber = null)
    {
        $this->mineType = $mineType;
        $this->plateNumber = $plateNumber;
    }

    /**
     * @param string $mineType
     * @return Registration
     */
    public static function createFromMineType(string $mineType): Registration
    {
        return new self($mineType);
    }

    /**
     * @param string $plateNumber
     * @return Registration
     */
    public static function createFromPlateNumber(string $plateNumber): Registration
    {
        return new self(null, $plateNumber);
    }


}
