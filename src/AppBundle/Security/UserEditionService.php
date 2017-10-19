<?php

namespace AppBundle\Security;

use AppBundle\Doctrine\Repository\InformationsUpdatable;
use AppBundle\Entity\ApplicationUser;
use AppBundle\Form\DTO\EditUserData;
use Wamcar\User\UserRepository;


class UserEditionService
{
    /** @var UserRepository  */
    private $userRepository;

    /**
     * UserEditionService constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param EditUserData $editUserData
     * @return ApplicationUser
     * @throws \Exception
     */
    public function editInformations(EditUserData $editUserData): ApplicationUser
    {
        if (!$this->userRepository instanceof InformationsUpdatable) {
            throw new \Exception('UserRespository must be "InformationsUpdatable" to edit informations');
        }

        $user = $this->userRepository->updateInformations($editUserData);

        return $user;
    }
}
