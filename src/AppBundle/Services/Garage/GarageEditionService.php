<?php

namespace AppBundle\Services\Garage;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Exception\Garage\AlreadyGarageMemberException;
use AppBundle\Exception\Garage\ExistingGarageException;
use AppBundle\Exception\Vehicle\NewSellerToAssignNotFoundException;
use AppBundle\Form\Builder\Garage\GarageFromDTOBuilder;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Services\User\CanBeGarageMember;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use GoogleApi\GoogleMapsApiConnector;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\Garage\Enum\GarageRole;
use Wamcar\Garage\Event\GarageMemberAssignedEvent;
use Wamcar\Garage\Event\GarageMemberUnassignedEvent;
use Wamcar\Garage\Event\GarageUpdated;
use Wamcar\Garage\Event\PendingRequestToJoinGarageAcceptedEvent;
use Wamcar\Garage\Event\PendingRequestToJoinGarageCancelledEvent;
use Wamcar\Garage\Event\PendingRequestToJoinGarageCreatedEvent;
use Wamcar\Garage\Event\PendingRequestToJoinGarageDeclinedEvent;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;
use Wamcar\Garage\GarageProUserRepository;
use Wamcar\Garage\GarageRepository;
use Wamcar\User\Event\EmailsInvitationsEvent;
use Wamcar\User\UserRepository;
use Wamcar\Vehicle\ProVehicle;


class GarageEditionService
{
    const INVITATION_EMAIL_ATTACHED = 'INVITATION_EMAIL_ATTACHED';
    const INVITATION_EMAIL_INVITED = 'INVITATION_EMAIL_INVITED';
    const INVITATION_EMAIL_PERSONAL = 'INVITATION_EMAIL_PERSONAL';

    /** @var GarageRepository */
    private $garageRepository;
    /** @var GarageProUserRepository */
    private $garageProUserRepository;
    /** @var UserRepository */
    private $userRepository;
    /** @var GarageFromDTOBuilder */
    private $garageBuilder;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;
    /** @var GoogleMapsApiConnector */
    private $googleMapsApi;
    /** @var MessageBus */
    protected $eventBus;

    /**
     * GarageEditionService constructor.
     * @param GarageRepository $garageRepository
     * @param GarageProUserRepository $garageProUserRepository
     * @param UserRepository $userRepository
     * @param GarageFromDTOBuilder $garageBuilder
     * @param ProVehicleEditionService $proVehicleEditionService
     * @param GoogleMapsApiConnector $googleMapsApiConnector
     * @param MessageBus $eventBus
     */
    public function __construct(
        GarageRepository $garageRepository,
        GarageProUserRepository $garageProUserRepository,
        UserRepository $userRepository,
        GarageFromDTOBuilder $garageBuilder,
        ProVehicleEditionService $proVehicleEditionService,
        GoogleMapsApiConnector $googleMapsApiConnector,
        MessageBus $eventBus
    )
    {
        $this->garageRepository = $garageRepository;
        $this->garageProUserRepository = $garageProUserRepository;
        $this->userRepository = $userRepository;
        $this->garageBuilder = $garageBuilder;
        $this->proVehicleEditionService = $proVehicleEditionService;
        $this->googleMapsApi = $googleMapsApiConnector;
        $this->eventBus = $eventBus;
    }

    /**
     * @param $user
     * @param Garage $garage
     * @return bool
     */
    public function canEdit($user, Garage $garage): bool
    {
        return $user instanceof CanBeGarageMember && $user->isAdministratorOfGarage($garage);
    }

    /**
     * @param $user
     * @param Garage $garage
     * @return bool
     */
    public function canAdministrate($user, Garage $garage): bool
    {
        return $user instanceof CanBeGarageMember && $user->isAdministratorOfGarage($garage);
    }

    /**
     * @param GarageDTO $garageDTO
     * @param null|Garage $garage
     * @param CanBeGarageMember $creator
     * @return Garage
     * @throws AlreadyGarageMemberException|ExistingGarageException
     */
    public function editInformations(GarageDTO $garageDTO, ?Garage $garage, CanBeGarageMember $creator): Garage
    {
        $existingGarage = null;

        if (!empty($garageDTO->googlePlaceId) && ($garage == null || $garage->getGooglePlaceId() !== $garageDTO->googlePlaceId)) {
            $existingGarage = $this->garageRepository->findOneBy(['googlePlaceId' => $garageDTO->googlePlaceId]);
        }
        if ($existingGarage === null && ($garage == null || $garage->getName() !== $garageDTO->name)) {
            $existingGarage = $this->garageRepository->findOneBy(['name' => $garageDTO->name]);
        }

        if ($existingGarage != null) {
            // Garage is already existing, 2 exceptions => 2 different messages
            if ($creator->isMemberOfGarage($existingGarage)) {
                throw new AlreadyGarageMemberException($existingGarage);
            } else {
                throw new ExistingGarageException($existingGarage);
            }
        } else {
            /** @var Garage $garage */
            $garage = $this->garageBuilder->buildFromDTO($garageDTO, $garage);

            if (!$creator->isMemberOfGarage($garage)) {
                $this->addMember($garage, $creator, true, true);
            }

            $garage = $this->garageRepository->update($garage);
            $this->eventBus->handle(new GarageUpdated($garage));

            return $garage;
        }
    }

    /**
     * @param Garage $garage
     * @param ProApplicationUser $proUser
     * @param boolean $isAdministrator
     * @param boolean $isEnabled
     * @return null|GarageProUser
     */
    public function addMember(Garage $garage, ProApplicationUser $proUser, bool $isAdministrator = false, bool $isEnabled = false): ?GarageProUser
    {
        if (!in_array('ROLE_ADMIN', $proUser->getRoles())) {
            /** @var GarageProUser $garageProUser */
            $garageProUser = new GarageProUser($garage, $proUser, $isAdministrator ? GarageRole::GARAGE_ADMINISTRATOR() : GarageRole::GARAGE_MEMBER());
            if (!$isEnabled) {
                $garageProUser->setRequestedAt(new \DateTime());
            }
            if (count($garage->getMembers()) == 0) {
                // Assingation by an ROLE_ADMIN, the first member is administrator
                $garageProUser->setRole(GarageRole::GARAGE_ADMINISTRATOR());
            }
            $garage->addMember($garageProUser);
            $proUser->addGarageMembership($garageProUser);
            $this->garageRepository->update($garage);
            $this->eventBus->handle(new GarageUpdated($garageProUser->getGarage()));
            if (!$isAdministrator) {
                if ($isEnabled) {
                    $this->eventBus->handle(new GarageMemberAssignedEvent($garageProUser));
                } else {
                    $this->eventBus->handle(new PendingRequestToJoinGarageCreatedEvent($garageProUser));
                }
            }
            return $garageProUser;
        }
        return null;
    }

    /**
     * @param Garage $garage
     * @param array $emails Array of emails to invite to the garage
     * @return array Result for each emails
     */
    public function inviteMember(Garage $garage, array $emails): array
    {
        $results = [
            self::INVITATION_EMAIL_ATTACHED => [],
            self::INVITATION_EMAIL_INVITED => [],
            self::INVITATION_EMAIL_PERSONAL => []
        ];
        $emailInvitationsToSend = [];
        foreach ($emails as $email) {
            $user = $this->userRepository->findOneByEmail($email);
            if ($user instanceof ProApplicationUser) {
                if ($user->isMemberOfGarage($garage, true)) {
                    $membership = $user->getMembershipByGarage($garage);
                    if ($membership->getRequestedAt() != null) {
                        // pending request
                        $this->acceptPendingRequest($membership);
                    }
                } else {
                    $this->addMember($garage, $user, false, true);
                }
                $results[self::INVITATION_EMAIL_ATTACHED][$email] = $user;
            } elseif ($user instanceof PersonalApplicationUser) {
                $results[self::INVITATION_EMAIL_PERSONAL][$email] = $user;
            } else {
                $emailInvitationsToSend[] = $email;
                $results[self::INVITATION_EMAIL_INVITED][] = $email;
            }
        }

        if (count($emailInvitationsToSend)) {
            $this->eventBus->handle(new EmailsInvitationsEvent($emailInvitationsToSend, $garage));
        }

        return $results;
    }

    /**
     * Accept (set requestedAt to null) a pending request
     * @param GarageProUser $garageProUser
     * @return GarageProUser
     */
    public function acceptPendingRequest(GarageProUser $garageProUser)
    {
        $garageProUser->setRequestedAt(null);
        $this->garageProUserRepository->update($garageProUser);
        $this->eventBus->handle(new GarageUpdated($garageProUser->getGarage()));
        $this->eventBus->handle(new PendingRequestToJoinGarageAcceptedEvent($garageProUser));
        return $garageProUser;
    }

    /**
     * Warning : All $proApplicationser's pro vehicles have to be reassigned to an other garage member
     * @param Garage $garage
     * @param ProApplicationUser $proApplicationUser
     * @param bool $isPendingRequestDeclined
     * @throws  \InvalidArgumentException if the user is member of the garage or has pro vehicle to sell
     */
    public function removeMember(Garage $garage, ProApplicationUser $proApplicationUser, bool $isPendingRequestDeclined = false)
    {
        /** @var GarageProUser $member */
        $member = $proApplicationUser->getMembershipByGarage($garage);
        if (null === $member) {
            throw new \InvalidArgumentException('User should be member of the garage');
        }
        if (count($member->getProUser()->getVehiclesOfGarage($garage)) > 0) {
            throw new \InvalidArgumentException('User should not have vehicle to sell');
        }
        $wasPendingRequest = $member->getRequestedAt() != null;
        $garage->removeMember($member);
        $this->garageProUserRepository->remove($member);
        $this->garageRepository->update($garage);
        $this->eventBus->handle(new GarageUpdated($garage));
        if ($wasPendingRequest) {
            if ($isPendingRequestDeclined) {
                $this->eventBus->handle(new PendingRequestToJoinGarageDeclinedEvent($member));
            } else {
                $this->eventBus->handle(new PendingRequestToJoinGarageCancelledEvent($member));
            }
        } else {
            $this->eventBus->handle(new GarageMemberUnassignedEvent($member));
        }
    }

    /**
     * Remove a GarageProUser, and if necessaery re-assign vehicles
     * @param GarageProUser $garageMemberShip
     * @param ApplicationUser $currentUser
     * @return array
     */
    public function removeMemberShip(GarageProUser $garageMemberShip, ApplicationUser $currentUser): array
    {
        $result = [
            'memberRemovedErrorMessage' => null,
            'vehiclesNotReassignedErrorMessages' => [],
            'memberRemovedSuccessMessage' => null
        ];
        try {
            if ($garageMemberShip->getRequestedAt() == null) {
                // Unassign garage member
                if (GarageRole::GARAGE_ADMINISTRATOR()->equals($garageMemberShip->getRole())) {
                    $result['memberRemovedErrorMessage'] = 'flash.error.garage.remove_administrator';
                } else {
                    $userVehicles = $garageMemberShip->getProUser()->getVehiclesOfGarage($garageMemberShip->getGarage());
                    if (count($userVehicles) > 0) {
                        // Vehicle distribution to other garage members

                        /** @var ProVehicle $vehicle */
                        foreach ($userVehicles as $vehicle) {
                            try {
                                $this->proVehicleEditionService->assignSeller($vehicle);
                            } catch (NewSellerToAssignNotFoundException $e) {
                                $result['vehiclesNotReassignedErrorMessages'][] = 'flash.error.vehicle.seller_to_reassign_not_found';
                            } catch (\InvalidArgumentException $e) {
                                $result['vehiclesNotReassignedErrorMessages'][] = $e->getMessage();
                            }
                        }

                        if (count($result['vehiclesNotReassignedErrorMessages']) == 0) {
                            $this->removeMember($garageMemberShip->getGarage(), $garageMemberShip->getProUser(), false);
                            $result['memberRemovedSuccessMessage'] = 'flash.success.garage.remove_member_with_reassignation';
                        }
                    } else {
                        $this->removeMember($garageMemberShip->getGarage(), $garageMemberShip->getProUser(), false);
                        $result['memberRemovedSuccessMessage'] = 'flash.success.garage.remove_member';
                    }
                }
            } else {
                // Pending request
                if ($garageMemberShip->getProUser()->is($currentUser)) {
                    // Cancelled by the proUser
                    $this->removeMember($garageMemberShip->getGarage(), $garageMemberShip->getProUser(), false);
                    $result['memberRemovedSuccessMessage'] = 'flash.success.garage.cancel_pending_request_by_user';
                } else {
                    // Declined by an administrator
                    $this->removeMember($garageMemberShip->getGarage(), $garageMemberShip->getProUser(), true);
                    $result['memberRemovedSuccessMessage'] = 'flash.success.garage.cancel_pending_request_by_administrator';
                }
            }
        } catch (\InvalidArgumentException $e) {
            $result['memberRemovedErrorMessage'][] = $e->getMessage();
        }
        return $result;
    }

    /**
     * @param Garage $garage
     * @throws \InvalidArgumentException from call to removeMember()
     */
    public function remove(Garage $garage)
    {
        $this->proVehicleEditionService->deleteAllForGarage($garage);
        /** @var GarageProUser $member */
        foreach ($garage->getMembers() as $member) {
            $this->removeMember($garage, $member->getProUser(), $member->getRequestedAt() != null);
        }
        // Remove the google place Id to allow a new garage creatioin
        $garage->setGooglePlaceId(null);
        $garage->setSiren(null);
        $garage->setName('DELETED' . $garage->getName());
        $this->garageRepository->update($garage);
        $this->garageRepository->remove($garage);
    }

    /**
     * @param GarageProUser $garageMemberShip The membership to toogle role
     * @param ApplicationUser $currentUser The current user
     * @return bool true if the toogle is effective, false otherwise
     * @throws \InvalidArgumentException
     */
    public function toogleRole(GarageProUser $garageMemberShip, ApplicationUser $currentUser){
        if($garageMemberShip->getProUser() === $currentUser){
            throw new \InvalidArgumentException('flash.error.garage.unable_to_toogle_role.yourself');
        }

        if(GarageRole::GARAGE_ADMINISTRATOR()->equals($garageMemberShip->getRole())){
            $currentAdministrators = $garageMemberShip->getGarage()->getAdministrators();
            if(count($currentAdministrators) == 1){
                throw new \InvalidArgumentException('flash.error.garage.unable_to_toogle_role.last_admin');
            }
            $garageMemberShip->setRole(GarageRole::GARAGE_MEMBER());
        }elseif(GarageRole::GARAGE_MEMBER()->equals($garageMemberShip->getRole())){
            $garageMemberShip->setRole(GarageRole::GARAGE_ADMINISTRATOR());
        }
        $garageMemberShip = $this->garageProUserRepository->update($garageMemberShip);
        return true;
    }

    /**
     * @param Garage $garage
     * @return null|array
     */
    public function getGooglePlaceDetails(Garage $garage)
    {
        if (!empty($garage->getGooglePlaceId())) {
            $googlePlaceDetails = $this->googleMapsApi->getPlaceDetails($garage->getGooglePlaceId());
            if ($googlePlaceDetails != null) {
                if (isset($googlePlaceDetails["rating"]) && $garage->getGoogleRating() !== $googlePlaceDetails["rating"]) {
                    $garage->setGoogleRating($googlePlaceDetails["rating"]);
                    $this->garageRepository->update($garage);
                    $this->eventBus->handle(new GarageUpdated($garage));
                }
            }
            return $googlePlaceDetails;
        }
        return null;
    }
}
