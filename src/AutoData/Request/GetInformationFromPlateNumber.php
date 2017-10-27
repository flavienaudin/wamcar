<?php

namespace AutoData\Request;

class GetInformationFromPlateNumber implements Request
{
    /** @var string */
    private $plateNumber;

    /**
     * GetInformationFromPlateNumber constructor.
     * @param string $plateNumber
     */
    public function __construct(string $plateNumber)
    {
        $this->plateNumber = $plateNumber;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'GetImmat';
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return ['Immat' => $this->plateNumber];
    }
}
