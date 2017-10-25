<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Security\Repository\RegisteredWithConfirmationProvider;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wamcar\User\UserRepository;

class DoctrinePersonalUserRepository extends EntityRepository implements UserRepository, RegisteredWithConfirmationProvider, UserProviderInterface
{
    use DoctrineUserRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function findOneByRegistrationToken($registrationToken): ApplicationUser
    {
        return $this->findOneBy(['registrationToken' => $registrationToken]);
    }

}
