<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Security\SecurityInterface\ApiUserProvider;
use AppBundle\Security\SecurityInterface\HasApiCredential;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wamcar\User\UserRepository;

class DoctrineUserRepository extends EntityRepository implements UserRepository, UserProviderInterface, ApiUserProvider
{
    use DoctrineUserRepositoryTrait;
    use SoftDeletableEntityRepositoryTrait;

    /**
     * @param string $clientId
     * @return HasApiCredential
     */
    public function getByClientId(string $clientId): ?HasApiCredential
    {
        return $this->findOneBy(['apiClientId' => $clientId]);
    }

}
