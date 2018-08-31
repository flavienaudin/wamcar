<?php

namespace AppBundle\Doctrine\Repository;


use AppBundle\Doctrine\Entity\UserPreferences;
use Doctrine\ORM\EntityRepository;
use Wamcar\User\UserPreferencesRepository;

class DoctrineUserPreferencesRepository extends EntityRepository implements UserPreferencesRepository
{
    /**
     * @inheritDoc
     */
    public function update(UserPreferences $userPreferences)
    {
        $userPreferences = $this->_em->merge($userPreferences);
        $this->_em->persist($userPreferences);
        $this->_em->flush();
    }

}