<?php

namespace AppBundle\Services\Garage;

use AppBundle\Form\Builder\Garage\GarageFromDTOBuilder;
use AppBundle\Form\DTO\GarageDTO;
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
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        GarageRepository $garageRepository,
        GarageProUserRepository $garageProUserRepository,
        GarageFromDTOBuilder $garageBuilder,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $this->garageRepository = $garageRepository;
        $this->garageProUserRepository = $garageProUserRepository;
        $this->garageBuilder = $garageBuilder;
    }

    /**
     * @param GarageDTO $garageDTO
     * @param null|Garage $garage
     * @return Garage
     */
    public function editInformations(GarageDTO $garageDTO, ?Garage $garage): Garage
    {
        /** @var Garage $garage */
        $garage = $this->garageBuilder->buildFromDTO($garageDTO, $garage);

        $garage = $this->garageRepository->update($garage);

        return $garage;
    }

    /**
     * @param Garage $garage
     * @param ProApplicationUser $proApplicationUser
     * @return Garage
     */
    public function addMember(Garage $garage, ProApplicationUser $proApplicationUser)
    {
        /** @var GarageProUser $garageProUser */
        $garageProUser = new GarageProUser($garage, $proApplicationUser);
        $garage->addMember($garageProUser);
        $this->garageRepository->update($garage);

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
