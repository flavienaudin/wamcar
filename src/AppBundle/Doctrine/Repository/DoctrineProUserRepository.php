<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Security\Repository\UserWithResettablePasswordProvider;
use AppBundle\Security\SecurityInterface\ApiUserProvider;
use AppBundle\Security\SecurityInterface\HasApiCredential;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wamcar\User\UserRepository;

class DoctrineProUserRepository extends EntityRepository implements UserRepository, UserProviderInterface, UserWithResettablePasswordProvider, ApiUserProvider
{
    use DoctrineUserRepositoryTrait;
    use PasswordResettableRepositoryTrait;

    /**
     * @param string $clientId
     * @return HasApiCredential
     */
    public function getByClientId(string $clientId): ?HasApiCredential
    {
        return $this->findOneBy(['apiClientId' => $clientId]);
    }


}
