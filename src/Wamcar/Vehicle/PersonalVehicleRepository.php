<?php

namespace Wamcar\Vehicle;


interface PersonalVehicleRepository extends VehicleRepository
{
    /**
     * @return mixed
     */
    public function retrieveVehiclesWithLessThan2PicturesSince24h();
}
