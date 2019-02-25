<?php

namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\User\BaseUser;
use Wamcar\User\ProLikeVehicle;
use Wamcar\User\UserLikeVehicleRepository;
use Wamcar\Vehicle\ProVehicle;

class DoctrineLikeProVehicleRepository extends EntityRepository implements UserLikeVehicleRepository
{
    use SoftDeletableEntityRepositoryTrait;
    use DoctrineUserLikeVehicleRepositoryTrait;


    /**
     * @param BaseUser $user
     * @param ProVehicle $vehicle
     * @return ProLikeVehicle|null
     */
    public function findOneByUserAndVehicle(BaseUser $user, ProVehicle $vehicle): ?ProLikeVehicle
    {
        return $this->findOneBy(['user' => $user, 'vehicle' => $vehicle]);
    }

    /**
     * @param ProVehicle $vehicle
     * @return ProLikeVehicle[]
     */
    public function findAllByVehicle(ProVehicle $vehicle): array
    {
        return $this->findBy(['vehicle' => $vehicle]);
    }
}