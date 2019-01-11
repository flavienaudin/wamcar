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

    public function findForSlugGeneration(bool $onlyEmptySlug = true, bool $includeDeleted = false): array
    {
        if ($includeDeleted) {
            $this->_em->getFilters()->disable('softDeleteable');
        }
        $qb = $this->createQueryBuilder('u');
        if ($onlyEmptySlug) {
            $qb->where($qb->expr()->orX(
                $qb->expr()->isNull('u.slug'),
                $qb->expr()->eq('u.slug', '?1')))
                ->setParameter(1, '');
        }
        $results = $qb->getQuery()->getResult();
        if ($includeDeleted) {
            $this->_em->getFilters()->enable('softDeleteable');
        }
        return $results;
    }
}
