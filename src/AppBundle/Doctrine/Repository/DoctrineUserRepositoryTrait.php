<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Doctrine\Entity\ApplicationUser;
use Wamcar\User\BaseUser;
use Symfony\Component\Security\Core\User\UserInterface;

trait DoctrineUserRepositoryTrait
{
    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->findBy([]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOne(int $userId): BaseUser
    {
        return $this->findOneBy(['id' => $userId]);
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

    /**
     * @param string $email
     * @return BaseUser
     */
    public function findOneByEmail(string $email)
    {
        return $this->findOneBy(['email' => $email]);
    }

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

}
