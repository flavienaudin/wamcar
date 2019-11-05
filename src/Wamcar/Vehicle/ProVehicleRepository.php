<?php

namespace Wamcar\Vehicle;


use Doctrine\Common\Collections\Collection;
use Wamcar\Garage\Garage;
use Wamcar\User\ProUser;

interface ProVehicleRepository extends VehicleRepository
{
    /**
     * @param $reference
     * @return mixed
     */
    public function findByReference($reference);

    /**
     * @param $reference
     * @param $vin
     * @return mixed
     */
    public function findOneByReferenceAndVIN($reference, $vin);

    /**
     * Return the $limit last vehicles
     * @param $limit
     * @return Collection
     */
    public function getLast($limit);

    /**
     * Return the $limit last vehicles
     * @param $limit
     * @return array
     */
    public function getLastWithPicture($limit);

    /**
     * @param Garage $garage
     * @param null|array $orderBy
     * @param null|bool $ignoreSoftDeleted
     * @return array
     */
    public function findAllForGarage(Garage $garage, array $orderBy = [], bool $ignoreSoftDeleted = false): array;

    /**
     * @param Garage $garage
     * @param array $references
     * @return Collection|array
     */
    public function findByGarageAndExcludedReferences(Garage $garage, array $references);

    /**
     * @param ProUser $proUser
     * @param array $params
     * @return Collection|array
     */
    public function getDeletedProVehiclesByRequest(ProUser $proUser, array $params);

    /**
     * @param ProUser $proUser
     * @param int|null $sinceDays
     * @param \DateTimeInterface|null $referenceDate
     * @return array
     */
    public function findDeletedProVehiclesByProUser(ProUser $proUser, ?int $sinceDays = 60, ?\DateTimeInterface $referenceDate = null): array;

    /**
     * @param int|null $months
     * @return array
     */
    public function findSoftDeletedForXMonth(?int $months = 3): array;
}
