<?php

namespace AppBundle\Services\Garage;

use AppBundle\Form\Builder\Garage\GarageFromDTOBuilder;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Services\User\CanBeGarageMember;
use Wamcar\Garage\Garage;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wamcar\Garage\GarageProUser;
use Wamcar\Garage\GarageProUserRepository;
use Wamcar\Garage\GarageRepository;


class GarageEditionService
{
    /** @var GarageRepository  */
    private $garageRepository;
    /** @var GarageProUserRepository  */
    private $garageProUserRepository;
    /** @var GarageFromDTOBuilder  */
    private $garageBuilder;
    /**
     * GarageEditionService constructor.
     * @param GarageRepository $garageRepository
     * @param GarageProUserRepository $garageProUserRepository
     * @param GarageFromDTOBuilder $garageBuilder
     */
    public function __construct(
        GarageRepository $garageRepository,
        GarageProUserRepository $garageProUserRepository,
        GarageFromDTOBuilder $garageBuilder
    )
    {
        $this->garageRepository = $garageRepository;
        $this->garageProUserRepository = $garageProUserRepository;
        $this->garageBuilder = $garageBuilder;
    }

    /**
     * @param $user
     * @param Garage $garage
     * @return bool
     */
    public function canEdit($user, Garage $garage): bool
    {
        return $user instanceof CanBeGarageMember && $user->isMemberOfGarage($garage);
    }

    /**
     * @param GarageDTO $garageDTO
     * @param null|Garage $garage
     * @param CanBeGarageMember $creator
     * @return Garage
     */
    public function editInformations(GarageDTO $garageDTO, ?Garage $garage, CanBeGarageMember $creator): Garage
    {
        /** @var Garage $garage */
        $garage = $this->garageBuilder->buildFromDTO($garageDTO, $garage);

        if(!$creator->isMemberOfGarage($garage)) {
            $this->addMember($garage, $creator);
        }

        $garage = $this->garageRepository->update($garage);

        return $garage;
    }

    /**
     * @param Garage $garage
     * @param ProApplicationUser $proApplicationUser
     * @return Garage
     */
    public function addMember(Garage $garage, ProApplicationUser $proApplicationUser): Garage
    {
        /** @var GarageProUser $garageProUser */
        if (!in_array('ROLE_ADMIN', $proApplicationUser->getRoles()))
        {
            $garageProUser = new GarageProUser($garage, $proApplicationUser);
            $garage->addMember($garageProUser);
            $this->garageRepository->update($garage);
        }

        return $garage;
    }

    /**
     * @param Garage $garage
     * @param ProApplicationUser $proApplicationUser
     * @return Garage
     */
    public function removeMember(Garage $garage, ProApplicationUser $proApplicationUser)
    {
        /** @var GarageProUser $member */
        $member = $proApplicationUser->getMembershipByGarage($garage);
        if (null === $member) {
            throw new \InvalidArgumentException('User should be member of the garage');
        }
        $garage->removeMember($member);
        $this->garageProUserRepository->remove($member);
        $this->garageRepository->update($garage);

        return $garage;
    }
}
