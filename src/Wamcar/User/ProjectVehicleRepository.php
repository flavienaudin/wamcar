<?php

namespace Wamcar\User;


interface ProjectVehicleRepository
{
    /**
     * @param ProjectVehicle $projectVehicle
     */
    public function remove(ProjectVehicle $projectVehicle): void;

}
