<?php

namespace AppBundle\Services\User;

use AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository;
use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Security\Repository\UserWithResettablePasswordProvider;


class UserGlobalSearchService
{
    /** @var DoctrinePersonalUserRepository */
    private $personalUserRepository;

    /** @var DoctrineProUserRepository */
    private $proUserRepository;

    /**
     * UserGlobalSearchService constructor.
     * @param DoctrinePersonalUserRepository $personalUserRepository
     * @param DoctrineProUserRepository $proUserRepository
     */
    public function __construct(
        DoctrinePersonalUserRepository $personalUserRepository,
        DoctrineProUserRepository $proUserRepository
    )
    {
        $this->personalUserRepository = $personalUserRepository;
        $this->proUserRepository = $proUserRepository;
    }

    /**
     * @param string $passwordResetToken
     * @return \AppBundle\Security\HasPasswordResettable|null
     * @throws \Exception
     */
    public function findOneByPasswordResetToken(string $passwordResetToken)
    {
        if (!$this->personalUserRepository instanceof UserWithResettablePasswordProvider && !$this->proUserRepository instanceof UserWithResettablePasswordProvider) {
            throw new \Exception('UserRepository must implement "UserWithResettablePasswordProvider" to be able to reset password');
        }

        if ($personalUser = $this->personalUserRepository->findOneByPasswordResetToken($passwordResetToken)) {
            return $personalUser;
        } elseif ($proUser = $this->proUserRepository->findOneByPasswordResetToken($passwordResetToken)) {
            return $proUser;
        } else {
            return null;
        }

    }
}
