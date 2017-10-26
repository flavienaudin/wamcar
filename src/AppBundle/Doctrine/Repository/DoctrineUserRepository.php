<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wamcar\User\UserRepository;

class DoctrineUserRepository extends EntityRepository implements UserRepository, UserProviderInterface
{
    use DoctrineUserRepositoryTrait;

}
