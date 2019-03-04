<?php

namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\User\UserLikeVehicleRepository;

class DoctrineUserLikeVehicleRepository extends EntityRepository implements UserLikeVehicleRepository
{
    use SoftDeletableEntityRepositoryTrait;
    use DoctrineUserLikeVehicleRepositoryTrait;
}