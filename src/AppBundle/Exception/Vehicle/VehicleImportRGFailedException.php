<?php


namespace AppBundle\Exception\Vehicle;


class VehicleImportRGFailedException extends \InvalidArgumentException
{
    /** @var string $rgName The name of the RG in error */
    private $rgName;

    /**
     * VehicleImportRGFailedException constructor.
     * @param string $rgName
     * @param int $dataIndex
     * @param null|string $mesage
     */
    public function __construct(string $rgName, ?string $mesage = "")
    {
        parent::__construct($mesage);
        $this->rgName = $rgName;
    }

    /**
     * @return string
     */
    public function getRgName(): string
    {
        return $this->rgName;
    }
}