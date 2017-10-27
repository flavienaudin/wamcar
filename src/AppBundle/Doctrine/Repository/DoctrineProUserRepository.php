<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Security\Repository\UserWithResettablePasswordProvider;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wamcar\User\UserRepository;

class DoctrineProUserRepository extends EntityRepository implements UserRepository, UserProviderInterface, UserWithResettablePasswordProvider
{
    use DoctrineUserRepositoryTrait;
    use PasswordResettableRepositoryTrait;

}
