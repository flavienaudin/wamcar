<?php

namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\User\BaseUser;
use Wamcar\User\PersonalLikeVehicle;
use Wamcar\User\UserLikeVehicleRepository;
use Wamcar\Vehicle\PersonalVehicle;

class DoctrineLikePersonalVehicleRepository extends EntityRepository implements UserLikeVehicleRepository
{
    use SoftDeletableEntityRepositoryTrait;
    use DoctrineUserLikeVehicleRepositoryTrait;


    /**
     * @param BaseUser $user
     * @param PersonalVehicle $vehicle
     * @return PersonalLikeVehicle|null
     */
    public function findOneByUserAndVehicle(BaseUser $user, PersonalVehicle $vehicle): ?PersonalLikeVehicle
    {

        return $this->findOneBy(['user' => $user, 'vehicle' => $vehicle]);
    }

    /**
     * @param PersonalVehicle $vehicle
     * @return PersonalLikeVehicle[]
     */
    public function findAllByVehicle(PersonalVehicle $vehicle): array
    {
        return $this->findBy(['vehicle' => $vehicle]);
    }
}