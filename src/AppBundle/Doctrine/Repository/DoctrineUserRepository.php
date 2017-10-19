<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Security\Repository\RegisteredWithConfirmationProvider;
use Doctrine\ORM\EntityRepository;
use Wamcar\User\BaseUser;
use Wamcar\User\BaseUserRepository;

class DoctrineUserRepository extends EntityRepository implements BaseUserRepository, RegisteredWithConfirmationProvider
{
    /**
     * {@inheritdoc}
     */
    public function findAll(): array
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
     * {@inheritdoc}
     */
    public function findOneByRegistrationToken($registrationToken): ApplicationUser
    {
        return $this->findOneBy(['registrationToken' => $registrationToken]);
    }
}
