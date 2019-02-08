<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Doctrine\Entity\ApplicationUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Wamcar\User\BaseUser;

trait DoctrineUserRepositoryTrait
{
    /**
     * @param string $username
     * @return null|ApplicationUser
     */
    public function loadUserByUsername($username): ?ApplicationUser
    {
        return $this->findOneBy(['email' => $username]);
    }

    /**
     * @param UserInterface $user
     * @return null|ApplicationUser
     */
    public function refreshUser(UserInterface $user): ?ApplicationUser
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return ApplicationUser::class === $class;
    }
    /** Fin des méthodes du UserProviderInterface */

    /**
     * {@inheritdoc}
     */
    public function findOne(int $userId): ?BaseUser
    {
        return $this->findOneBy(['id' => $userId]);
    }
    /**
     * @param string $email
     * @return BaseUser
     */
    public function findOneByEmail(string $email)
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @param $ids array Array of entities'id
     * @return array
     */
    public function findByIds(array $ids): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from($this->getClassName(), 'u')
            ->where($qb->expr()->in('u.id', $ids))
            ->orderBy($qb->expr()->asc('FIELD(u.id, :orderedIds ) '));
        $qb->setParameter('orderedIds', $ids);
        return $qb->getQuery()->getResult();
    }


    /**
     * {@inheritdoc}
     */
    public function add(BaseUser $user)
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(BaseUser $user)
    {
        $user = $this->_em->merge($user);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(BaseUser $user)
    {
        $this->_em->remove($user);
        $this->_em->flush();
    }
}
