<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Doctrine\Entity\AffinityDegree;
use Doctrine\ORM\EntityRepository;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;

class DoctrineAffinityDegreeRepository extends EntityRepository
{
    /**
     * @param ProUser $proUser
     * @param PersonalUser $personalUser
     * @return AffinityDegree|null
     */
    public function findOne(ProUser $proUser, PersonalUser $personalUser): ?AffinityDegree
    {
        return $this->findOneBy(['id' => ["proUser" => $proUser, "personalUser" => $personalUser]]);
    }
}