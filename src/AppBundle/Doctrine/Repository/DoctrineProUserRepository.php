<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Security\Repository\UserWithResettablePasswordProvider;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wamcar\User\UserRepository;

class DoctrineProUserRepository extends EntityRepository implements UserRepository, UserProviderInterface, UserWithResettablePasswordProvider
{
    use DoctrineUserRepositoryTrait;
    use SoftDeletableEntityRepositoryTrait;
    use PasswordResettableRepositoryTrait;
    use SluggableEntityRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function findProUsersForHomepage(): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where($qb->expr()->isNotNull('u.landingPosition'))
            ->orderBy('u.landingPosition', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
