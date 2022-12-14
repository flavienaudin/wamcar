<?php

namespace AppBundle\Services\Garage;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\GarageBanner;
use AppBundle\Doctrine\Entity\GarageLogo;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Exception\Garage\AlreadyGarageMemberException;
use AppBundle\Exception\Garage\ExistingGarageException;
use AppBundle\Form\Builder\Garage\GarageFromDTOBuilder;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\DTO\GaragePictureDTO;
use AppBundle\Form\DTO\GaragePresentationDTO;
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
        MessageBus $eventBus)
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
        // Pr??caution
        if ($garageDTO->googlePlaceId == "undefined") {
            $garageDTO->googlePlaceId = null;
        }
        if (!empty($garageDTO->googlePlaceId) && ($garage == null || $garage->getGooglePlaceId() !== $garageDTO->googlePlaceId)) {
            $existingGarage = $this->garageRepository->findOneBy(['googlePlaceId' => $garageDTO->googlePlaceId]);
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
     * @param GaragePictureDTO $garagePictureDTO
     * @param Garage $garage
     * @return Garage
     */
    public function editBanner(GaragePictureDTO $garagePictureDTO, Garage $garage): Garage
    {
        if ($garagePictureDTO->isRemoved) {
            $garage->removeBanner();
        } else {
            $banner = new GarageBanner($garage, $garagePictureDTO->file);
            $garage->setBanner($banner);
        }
        $garage = $this->garageRepository->update($garage);
        $this->eventBus->handle(new GarageUpdated($garage));
        return $garage;
    }

    /**
     * @param GaragePictureDTO $garagePictureDTO
     * @param Garage $garage
     * @return Garage
     */
    public function editLogo(GaragePictureDTO $garagePictureDTO, Garage $garage): Garage
    {
        if ($garagePictureDTO->isRemoved) {
            $garage->removeLogo();
        } else {
            $logo = new GarageLogo($garage, $garagePictureDTO->file);
            $garage->setLogo($logo);
        }
        $garage = $this->garageRepository->update($garage);
        $this->eventBus->handle(new GarageUpdated($garage));
        return $garage;
    }

    /**
     * @param GaragePresentationDTO $garagePresentationDTO
     * @param Garage $garage
     * @return Garage
     */
    public function editPresentationInformations(GaragePresentationDTO $garagePresentationDTO, Garage $garage): Garage
    {
        $garage->setPresentation($garagePresentationDTO->presentation);
        $garage = $this->garageRepository->update($garage);
        $this->eventBus->handle(new GarageUpdated($garage));
        return $garage;
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
                // Assingation by an ROLE_PRO_ADMIN, the first member is administrator
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
     * Warnings :
     *  - No verification if $proApplicationser is the last member/admin of the garage
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
     * Remove a GarageProUser
     * @param GarageProUser $garageMemberShip
     * @param ApplicationUser $currentUser
     * @return array
     */
    public function removeMemberShip(GarageProUser $garageMemberShip, ApplicationUser $currentUser): array
    {
        $result = [
            'memberRemovedErrorMessage' => null,
            'memberRemovedSuccessMessage' => null
        ];
        try {
            if ($garageMemberShip->getRequestedAt() == null) {
                // Unassign garage member
                if (GarageRole::GARAGE_ADMINISTRATOR()->equals($garageMemberShip->getRole()) && count($garageMemberShip->getGarage()->getAdministrators()) <= 1) {
                    // Unique administrator
                    $result['memberRemovedErrorMessage'] = 'flash.error.garage.remove_administrator';
                } else {
                    $this->removeMember($garageMemberShip->getGarage(), $garageMemberShip->getProUser(), false);
                    $result['memberRemovedSuccessMessage'] = 'flash.success.garage.remove_member';
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
        // Remove the google place Id and SIREN, and rename the garage to allow a new garage creation using the same name, SIREN or/and google place Id
        $garage->setGooglePlaceId(null);
        $garage->setSiren(null);
        // Update for softdeletion
        $this->garageRepository->update($garage);
        $this->garageRepository->remove($garage);
    }

    /**
     * @param GarageProUser $garageMemberShip The membership to toogle role
     * @throws \InvalidArgumentException
     */
    public function toogleRole(GarageProUser $garageMemberShip)
    {
        if (GarageRole::GARAGE_ADMINISTRATOR()->equals($garageMemberShip->getRole())) {
            $currentAdministrators = $garageMemberShip->getGarage()->getAdministrators();
            if (count($currentAdministrators) == 1) {
                throw new \InvalidArgumentException('flash.error.garage.unable_to_toogle_role.last_admin');
            }
            $garageMemberShip->setRole(GarageRole::GARAGE_MEMBER());
        } elseif (GarageRole::GARAGE_MEMBER()->equals($garageMemberShip->getRole())) {
            $garageMemberShip->setRole(GarageRole::GARAGE_ADMINISTRATOR());
        }
        $this->garageProUserRepository->update($garageMemberShip);
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
