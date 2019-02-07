<?php

namespace AppBundle\Services\Garage;

use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Exception\Garage\AlreadyGarageMemberException;
use AppBundle\Exception\Garage\ExistingGarageException;
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
     * @param Garage $garage
     * @param ProApplicationUser $proApplicationUser
     * @param bool $isPendingRequestDeclined
     * @return Garage
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
        return $garage;
    }

    /**
     * @param Garage $garage
     */
    public function remove(Garage $garage)
    {
        $this->proVehicleEditionService->deleteAllForGarage($garage);
        /** @var GarageProUser $member */
        foreach ($garage->getMembers() as $member) {
            $this->removeMember($garage, $member->getProUser(), true);
        }
        // Remove the google place Id to allow a new garage creatioin
        $garage->setGooglePlaceId(null);
        $garage->setSiren(null);
        $garage->setName('DELETED' . $garage->getName());
        $this->garageRepository->remove($garage);
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
