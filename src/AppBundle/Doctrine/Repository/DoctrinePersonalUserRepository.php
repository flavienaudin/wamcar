<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Security\Repository\RegisteredWithConfirmationProvider;
use AppBundle\Security\Repository\UserWithResettablePasswordProvider;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wamcar\User\UserRepository;

class DoctrinePersonalUserRepository extends EntityRepository implements UserRepository, RegisteredWithConfirmationProvider, UserProviderInterface, UserWithResettablePasswordProvider
{
    use DoctrineUserRepositoryTrait;
    use PasswordResettableRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function findOneByRegistrationToken($registrationToken): ApplicationUser
    {
        return $this->findOneBy(['registrationToken' => $registrationToken]);
    }

}
