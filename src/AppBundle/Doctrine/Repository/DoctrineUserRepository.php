<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Entity\ApplicationUser;
use AppBundle\Form\DTO\EditUserData;
use AppBundle\Security\Repository\UserTokenable;
use Doctrine\ORM\EntityRepository;
use Wamcar\User\User;
use Wamcar\User\UserRepository;

class DoctrineUserRepository extends EntityRepository implements UserRepository, RegisteredWithConfirmationProvider
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
    public function findOne(int $userId): User
    {
        return $this->findOneBy(['id' => $userId]);
    }

    /**
     * {@inheritdoc}
     */
    public function add(User $user)
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(User $user)
    {
        $user = $this->_em->merge($user);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(User $user)
    {
        $this->_em->remove($user);
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
    public function findOneByRegistrationToken($registrationToken): ApplicationUser
    {
        return $this->findOneBy(['registrationToken' => $registrationToken]);
    }

    /**
     * {@inheritdoc}
     */
    public function updateInformations(EditUserData $userData): ApplicationUser
    {
        /**
         * @var ApplicationUser $user
         * Retrieve user
         */
        $user = $this->findOne($userData->id);
        // update informations
        $user->updateInformations($userData);
        // and save modification
        $this->update($user);

        return $user;
    }


}
