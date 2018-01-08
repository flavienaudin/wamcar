<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\User\ProjectVehicle;
use Wamcar\User\ProjectVehicleRepository;

class DoctrineProjectVehicleRepository extends EntityRepository implements ProjectVehicleRepository
{

    /**
     * {@inheritdoc}
     */
    public function remove(ProjectVehicle $projectVehicle): void
    {
        $this->_em->remove($projectVehicle);
        $this->_em->flush();
    }
}
