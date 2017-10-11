<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\User\User;
use Wamcar\User\UserRepository;

class DoctrineUserRepository extends EntityRepository implements UserRepository, RegistrationTokenable
{
    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return $this->findBy([
            'deletedAt' => NULL,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOne(int $userId): User
    {
        return $this->findOneBy(['id' => $userId->getValue()]);
    }

    /**
     * {@inheritdoc}
     */
    public function add(User $user): User
    {
        $this->_em->persist($user);
        $this->_em->flush();

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function update(User $user): User
    {
        $user = $this->_em->merge($user);
        $this->_em->persist($user);
        $this->_em->flush();

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(User $user, bool $hardDelete = false)
    {
        if ($hardDelete) {
            $this->_em->remove($user);
        } else {
            $user->setDeletedAt(new \DateTime());
            $this->_em->persist($user);
        }

        $this->_em->flush();
    }

    /**
     * @param string $email
     * @return User
     */
    public function findOneByEmail(string $email)
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByRegistrationToken($registrationToken) {
        return $this->findOneBy(['registrationToken' => $registrationToken]);
    }

}
