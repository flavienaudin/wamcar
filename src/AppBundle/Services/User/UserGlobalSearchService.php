<?php

namespace AppBundle\Services\User;

use AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository;
use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Security\Repository\UserWithResettablePasswordProvider;
use Wamcar\User\PersonalUser;


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


    /**
     * Search for new personal user (registration) between the $refDatetime or now() - 1H, and the $refDatetime or now() - 1H - $delay H
     * The personal user
     * @param int $since
     * @param \DateTime|null $refDatetime
     * @return array
     * @throws \Exception
     */
    public function findNewPersonalUser(int $since, ?\DateTime $refDatetime = null): array
    {
        return $this->personalUserRepository->findNewRegistations($since, $refDatetime);
    }

    /**
     * Seach for ProUsers according to Pro's preferences and the given PersonalUser : localization, personal vehicle
     * and project
     * @param PersonalUser $personalUser
     * @return array
     */
    public function retrieveProUserToInform(PersonalUser $personalUser): array
    {
        return $this->proUserRepository->findInterestedByPersonalUser($personalUser);
    }

}
