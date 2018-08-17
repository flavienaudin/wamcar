<?php

namespace Wamcar\Vehicle;

use Doctrine\Common\Collections\Collection;
use Wamcar\Garage\Garage;
use Wamcar\User\BaseUser;

interface VehicleRepository
{
    /**
     * @param Vehicle $vehicle
     */
    public function add(Vehicle $vehicle): void;

    /**
     * @param Vehicle $vehicle
     */
    public function update(Vehicle $vehicle): void;

    /**
     * @param Vehicle $vehicle
     */
    public function remove(Vehicle $vehicle): void;

    /**
     * @param Garage $garage
     * @return array
     */
    public function findAllForGarage(Garage $garage): array;

    /**
     * @param Garage $garage
     */
    public function deleteAllForGarage(Garage $garage): void;

    /**
     * @param string $id
     * @return Vehicle
     */
    public function find($id);


    /**
     * @param BaseUser $user
     * @param string $text
     * @return Collection
     */
    public function findByTextSearch(BaseUser $user, string $text): Collection;
}
