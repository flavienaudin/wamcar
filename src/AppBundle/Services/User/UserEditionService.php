<?php

namespace AppBundle\Services\User;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Doctrine\Entity\UserBanner;
use AppBundle\Doctrine\Entity\UserPicture;
use AppBundle\Elasticsearch\Type\IndexablePersonalProject;
use AppBundle\Elasticsearch\Type\IndexableSearchItem;
use AppBundle\Form\Builder\User\ProjectFromDTOBuilder;
use AppBundle\Form\DTO\ProjectDTO;
use AppBundle\Form\DTO\ProPresentationVideoDTO;
use AppBundle\Form\DTO\ProUserInformationDTO;
use AppBundle\Form\DTO\ProUserPresentationDTO;
use AppBundle\Form\DTO\ProUserProSpecialitiesDTO;
use AppBundle\Form\DTO\RegistrationDTO;
use AppBundle\Form\DTO\UserInformationDTO;
use AppBundle\Form\DTO\UserPasswordDTO;
use AppBundle\Form\DTO\UserPreferencesDTO;
use AppBundle\Form\DTO\UserPresentationDTO;
use AppBundle\Security\HasPasswordResettable;
use AppBundle\Security\Repository\UserWithResettablePasswordProvider;
use AppBundle\Security\UserRegistrationService;
use AppBundle\Services\Garage\GarageEditionService;
use AppBundle\Services\Vehicle\PersonalVehicleEditionService;
use AppBundle\Utils\TokenGenerator;
use Elastica\ResultSet;
use Novaway\ElasticsearchClient\Query\Result;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Wamcar\Conversation\ConversationUser;
use Wamcar\Conversation\Message;
use Wamcar\Garage\Enum\GarageRole;
use Wamcar\Garage\GarageProUser;
use Wamcar\Location\City;
use Wamcar\User\BaseLikeVehicle;
use Wamcar\User\BaseUser;
use Wamcar\User\Enum\PersonalOrientationChoices;
use Wamcar\User\Event\PersonalProjectRemoved;
use Wamcar\User\Event\PersonalProjectUpdated;
use Wamcar\User\Event\PersonalUserRemoved;
use Wamcar\User\Event\PersonalUserUpdated;
use Wamcar\User\Event\ProUserPublished;
use Wamcar\User\Event\ProUserRemoved;
use Wamcar\User\Event\ProUserUnpublished;
use Wamcar\User\Event\ProUserUpdated;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProjectRepository;
use Wamcar\User\ProUser;
use Wamcar\User\ProUserProService;
use Wamcar\User\ProUserProServiceRepository;
use Wamcar\User\UserLikeVehicleRepository;
use Wamcar\User\UserPreferencesRepository;
use Wamcar\User\UserRepository;
use Wamcar\User\VideosInsertReposistory;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\PersonalVehicleRepository;
use Wamcar\Vehicle\ProVehicleRepository;


class UserEditionService
{
    /** @var PasswordEncoderInterface */
    private $passwordEncoder;
    /** @var UserRepository */
    private $userRepository;
    /** @var array */
    private $userSpecificRepositories;
    /** @var ProjectFromDTOBuilder */
    private $projectBuilder;
    /** @var ProjectRepository */
    private $projectRepository;
    /** @var PersonalVehicleEditionService */
    private $personalVehicleEditionService;
    /** @var PersonalVehicleRepository */
    private $personalVehicleRepository;
    /** @var ProVehicleRepository */
    private $proVehicleRepository;
    /** @var UserPreferencesRepository */
    private $userPreferencesRepository;
    /** @var ProUserProServiceRepository */
    private $proUserProServiceRepository;
    /** @var VideosInsertReposistory */
    private $videosInsertRepository;
    /** @var GarageEditionService */
    private $garageEditionService;
    /** @var UserLikeVehicleRepository $userLikeRepository */
    private $userLikeRepository;
    /** @var UserRegistrationService */
    private $userRegistrationService;
    /** @var MessageBus */
    private $eventBus;

    /**
     * UserEditionService constructor.
     * @param PasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     * @param array $userSpecificRepositories
     * @param ProjectFromDTOBuilder $projectBuilder
     * @param ProjectRepository $projectRepository
     * @param PersonalVehicleEditionService $personalVehicleEditionService
     * @param PersonalVehicleRepository $personalVehicleRepository
     * @param ProVehicleRepository $proVehicleRepository
     * @param UserPreferencesRepository $userPreferencesRepository
     * @param ProUserProServiceRepository $proUserProServiceRepository
     * @param VideosInsertReposistory $videosInsertRepository
     * @param GarageEditionService $garageEditionService
     * @param UserLikeVehicleRepository $userLikeRepository
     * @param UserRegistrationService $userRegistrationService
     * @param MessageBus $eventBus
     */
    public function __construct(
        PasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        array $userSpecificRepositories,
        ProjectFromDTOBuilder $projectBuilder,
        ProjectRepository $projectRepository,
        PersonalVehicleEditionService $personalVehicleEditionService,
        PersonalVehicleRepository $personalVehicleRepository,
        ProVehicleRepository $proVehicleRepository,
        UserPreferencesRepository $userPreferencesRepository,
        ProUserProServiceRepository $proUserProServiceRepository,
        VideosInsertReposistory $videosInsertRepository,
        GarageEditionService $garageEditionService,
        UserLikeVehicleRepository $userLikeRepository,
        UserRegistrationService $userRegistrationService,
        MessageBus $eventBus
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->userSpecificRepositories = $userSpecificRepositories;
        $this->projectBuilder = $projectBuilder;
        $this->projectRepository = $projectRepository;
        $this->personalVehicleEditionService = $personalVehicleEditionService;
        $this->personalVehicleRepository = $personalVehicleRepository;
        $this->proVehicleRepository = $proVehicleRepository;
        $this->userPreferencesRepository = $userPreferencesRepository;
        $this->proUserProServiceRepository = $proUserProServiceRepository;
        $this->videosInsertRepository = $videosInsertRepository;
        $this->garageEditionService = $garageEditionService;
        $this->userLikeRepository = $userLikeRepository;
        $this->userRegistrationService = $userRegistrationService;
        $this->eventBus = $eventBus;
    }

    /**
     * @param BaseUser $user
     * @param UserInformationDTO $userInformationDTO
     * @return BaseUser
     * @throws \Exception
     */
    public function editInformations(BaseUser $user, UserInformationDTO $userInformationDTO): ApplicationUser
    {
        if (!empty($userInformationDTO->newPassword)) {
            $isValid = $this->passwordEncoder->isPasswordValid($user->getPassword(), $userInformationDTO->oldPassword, $user->getSalt());
            if (!$isValid) {
                throw new \InvalidArgumentException('Password should be the current');
            }
            $this->editPassword($user, $userInformationDTO->newPassword);
        }

        $user->setEmail($userInformationDTO->email);
        $user->updateUserProfile($userInformationDTO->getUserProfile());

        if ($userInformationDTO->avatar) {
            if ($userInformationDTO->avatar->isRemoved) {
                $user->setAvatar(null);
            } elseif ($userInformationDTO->avatar->file) {
                $picture = new UserPicture($user, $userInformationDTO->avatar->file);
                $user->setAvatar($picture);
            }
        }

        if ($user instanceof ProUser && $userInformationDTO instanceof ProUserInformationDTO) {
            $user->setPhonePro($userInformationDTO->phonePro);
            $user->setPresentationTitle($userInformationDTO->presentationTitle);

            if ($userInformationDTO->banner) {
                if ($userInformationDTO->banner->isRemoved) {
                    $user->setBanner(null);
                } elseif ($userInformationDTO->banner->file) {
                    $picture = new UserBanner($user, $userInformationDTO->banner->file);
                    $user->setBanner($picture);
                }
            }
        }

        $this->userRepository->update($user);
        if ($user instanceof PersonalUser) {
            $this->eventBus->handle(new PersonalUserUpdated($user));
            if ($user->getProject() != null) {
                $this->eventBus->handle(new PersonalProjectUpdated($user->getProject()));
            }
        } else if ($user instanceof ProUser) {
            $this->eventBus->handle(new ProUserUpdated($user));
        }
        return $user;
    }

    /**
     * @param BaseUser $user
     * @param UserInformationDTO $userInformationDTO
     * @return BaseUser
     */
    public function editAvatar(BaseUser $user, UserInformationDTO $userInformationDTO): BaseUser
    {
        if ($userInformationDTO->avatar) {
            if ($userInformationDTO->avatar->isRemoved) {
                $user->setAvatar(null);
            } elseif ($userInformationDTO->avatar->file) {
                $picture = new UserPicture($user, $userInformationDTO->avatar->file);
                $user->setAvatar($picture);
            }
        }

        $this->userRepository->update($user);
        if ($user instanceof PersonalUser) {
            $this->eventBus->handle(new PersonalUserUpdated($user));
            if ($user->getProject() != null) {
                $this->eventBus->handle(new PersonalProjectUpdated($user->getProject()));
            }
        } else if ($user instanceof ProUser) {
            $this->eventBus->handle(new ProUserUpdated($user));
        }
        return $user;
    }

    /**
     * @param ProUser $user
     * @param ProUserInformationDTO $proUserInformationDTO
     * @return ProUser
     */
    public function editUserBanner(ProUser $user, ProUserInformationDTO $proUserInformationDTO): ProUser
    {
        if ($proUserInformationDTO->banner) {
            if ($proUserInformationDTO->banner->isRemoved) {
                $user->setBanner(null);
            } elseif ($proUserInformationDTO->banner->file) {
                $picture = new UserBanner($user, $proUserInformationDTO->banner->file);
                $user->setBanner($picture);
            }
        }

        $this->userRepository->update($user);
        $this->eventBus->handle(new ProUserUpdated($user));
        return $user;
    }


    /**
     * @param ProUser $user
     * @param ProUserPresentationDTO $proUserPresentationDTO
     * @return BaseUser
     */
    public function editContactDetails(ProUser $user, ProUserInformationDTO $proUserInformationDTO)
    {
        $user->getUserProfile()->setTitle($proUserInformationDTO->title);
        $user->getUserProfile()->setFirstName($proUserInformationDTO->firstName);
        $user->getUserProfile()->setLastName($proUserInformationDTO->lastName);
        $user->getUserProfile()->setPhone($proUserInformationDTO->phone);
        $user->setPhonePro($proUserInformationDTO->phonePro);

        $this->userRepository->update($user);
        $this->eventBus->handle(new ProUserUpdated($user));
        return $user;
    }

    /**
     * @param BaseUser $user
     * @param UserPresentationDTO $userPresentationDTO
     * @return BaseUser
     */
    public function editPresentationInformations(BaseUser $user, UserPresentationDTO $userPresentationDTO)
    {
        $user->setDescription($userPresentationDTO->description);
        if ($userPresentationDTO instanceof ProUserPresentationDTO && $user instanceof ProUser) {
            $user->setPresentationTitle($userPresentationDTO->presentationTitle);
        }
        $this->userRepository->update($user);
        if ($user instanceof PersonalUser) {
            $this->eventBus->handle(new PersonalUserUpdated($user));
        } else if ($user instanceof ProUser) {
            $this->eventBus->handle(new ProUserUpdated($user));
        }
        return $user;
    }

    /**
     * @param BaseUser $user
     * @param ProPresentationVideoDTO $proPresentationVideoDTO
     * @return BaseUser
     */
    public function editVideoInformations(BaseUser $user, ProPresentationVideoDTO $proPresentationVideoDTO)
    {
        $user->setYoutubeVideoUrl($proPresentationVideoDTO->youtubeVideoUrl);
        $user->setVideoTitle($proPresentationVideoDTO->videoTitle);
        $user->setVideoText($proPresentationVideoDTO->videoText);
        $this->userRepository->update($user);
        return $user;
    }

    /**
     * @param ProUser $proUser
     * @return ProUser
     */
    public function publishProUserProfile(ProUser $proUser)
    {
        $proUser->setAskForPublication(true);
        $this->userRepository->update($proUser);
        $this->eventBus->handle(new ProUserPublished($proUser));
        return $proUser;
    }

    /**
     * @param ProUser $proUser
     * @return ProUser
     */
    public function unpublishProUserProfile(ProUser $proUser)
    {
        $proUser->setAskForPublication(false);
        $this->userRepository->update($proUser);
        $this->eventBus->handle(new ProUserUnpublished($proUser));
        return $proUser;
    }

    /**
     * @param BaseUser $userToDelete
     * @param ApplicationUser $currentUser
     * @param null|string $deletionReason
     * @return array with 'errorMessages', 'successMessages'
     */
    public function deleteUser(BaseUser $userToDelete, ApplicationUser $currentUser, ?string $deletionReason = null)
    {
        $isAlreadySofDeleted = $userToDelete->getDeletedAt() != null;
        $resultMessages = ['errorMessages' => [], 'successMessages' => []];

        if ($isAlreadySofDeleted &&
            (count($userToDelete->getConversationUsers()) > 0 || count($userToDelete->getMessages()) > 0)) {
            $resultMessages['errorMessages'][] = 'flash.error.user.deletion_with_conversations';
        } else {
            if ($isAlreadySofDeleted) {
                // Hard delete of the user likes
                $userLikes = $this->userLikeRepository->findIgnoreSoftDeletedBy(['user' => $userToDelete]);
                foreach ($userLikes as $userLike) {
                    $this->userLikeRepository->remove($userLike);
                }
            }

            if ($userToDelete instanceof ProApplicationUser) {

                /** @var GarageProUser $garageMembership */
                foreach ($userToDelete->getGarageMemberships() as $garageMembership) {
                    if (GarageRole::GARAGE_ADMINISTRATOR()->equals($garageMembership->getRole())) {
                        if (count($garageMembership->getGarage()->getMembers()) == 1) {
                            $this->garageEditionService->remove($garageMembership->getGarage());
                        } else {
                            $resultMessages['errorMessages'][] = 'flash.error.garage.still_administrator_with_member';
                        }
                    } else {
                        $garageId = $garageMembership->getGarage()->getId();
                        $deleteMembershipResult = $this->garageEditionService->removeMemberShip($garageMembership, $currentUser);
                        if ($deleteMembershipResult['memberRemovedErrorMessage'] != null) {
                            $resultMessages['errorMessages'][] = $deleteMembershipResult['memberRemovedErrorMessage'];
                        } elseif (count($deleteMembershipResult['vehiclesNotReassignedErrorMessages']) > 0) {
                            foreach ($deleteMembershipResult['vehiclesNotReassignedErrorMessages'] as $errorMessage) {
                                $resultMessages['errorMessages'][] = $errorMessage;
                            }
                        } elseif (!empty($deleteMembershipResult['memberRemovedSuccessMessage'])) {
                            $resultMessages['successMessages'][$garageId] = $deleteMembershipResult['memberRemovedSuccessMessage'];
                        }
                    }
                }
                if (count($resultMessages['errorMessages']) == 0) {
                    $this->userRepository->remove($userToDelete);
                    if (!$isAlreadySofDeleted) {
                        if (!empty($deletionReason)) {
                            $userToDelete->setDeletionReason($deletionReason);
                            $this->userRepository->update($userToDelete);
                        }
                        $this->eventBus->handle(new ProUserRemoved($userToDelete));
                    }
                }
            } elseif ($userToDelete instanceof PersonalApplicationUser) {
                // Delete personal vehicles
                if ($userToDelete->getDeletedAt() != null) {
                    $vehiclesToDeleted = $this->personalVehicleRepository->findIgnoreSoftDeletedBy(['owner' => $userToDelete]);
                } else {
                    $vehiclesToDeleted = $userToDelete->getVehicles();
                }

                /** @var BaseVehicle $vehicleToDelete */
                foreach ($vehiclesToDeleted as $vehicleToDelete) {
                    if ($userToDelete->getDeletedAt() != null || $vehicleToDelete->getDeletedAt() == null) {
                        $this->personalVehicleEditionService->deleteVehicle($vehicleToDelete);
                    }
                }

                $this->userRepository->remove($userToDelete);
                if (!$isAlreadySofDeleted) {
                    if (!empty($deletionReason)) {
                        $userToDelete->setDeletionReason($deletionReason);
                        $this->userRepository->update($userToDelete);
                    }
                    $this->eventBus->handle(new PersonalUserRemoved($userToDelete));
                    if ($userToDelete->getProject() != null) {
                        $this->eventBus->handle(new PersonalProjectRemoved($userToDelete->getProject()));
                    }
                }
            }
        }

        return $resultMessages;
    }

    /**
     * @param ApplicationUser $user
     * @param ProjectDTO $projectDTO
     * @return ApplicationUser
     * @throws \Exception
     */
    public function projectInformations(ApplicationUser $user, ProjectDTO $projectDTO): ApplicationUser
    {
        $project = $this->projectBuilder::buildFromDTO($projectDTO, $user);

        $this->projectRepository->update($project);
        return $user;
    }

    /**
     * @param HasPasswordResettable $user
     * @return HasPasswordResettable
     */
    public function generatePasswordResetToken(HasPasswordResettable $user): HasPasswordResettable
    {
        $user->generatePasswordResetToken();
        $this->userRepository->update($user);

        return $user;
    }

    /**
     * @param HasPasswordResettable $user
     * @param UserPasswordDTO $userPasswordDTO
     * @throws \Exception
     */
    public function editUserPassword(HasPasswordResettable $user, UserPasswordDTO $userPasswordDTO)
    {
        if (!empty($userPasswordDTO->newPassword)) {
            $isValid = $this->passwordEncoder->isPasswordValid($user->getPassword(), $userPasswordDTO->oldPassword, $user->getSalt());
            if (!$isValid) {
                throw new \InvalidArgumentException('Password should be the current');
            }
            $this->editPassword($user, $userPasswordDTO->newPassword);
        }
    }

    /**
     * @param HasPasswordResettable $user
     * @param $password
     *
     * @throws \Exception
     */
    public function editPassword(HasPasswordResettable $user, $password)
    {
        if (!$password) {
            throw new \InvalidArgumentException('Password should be set for password editing');
        }

        $salt = TokenGenerator::generateSalt();
        $encodedPassword = $this->passwordEncoder->encodePassword($password, $salt);

        $userSpecificRepository = $this->userSpecificRepositories[get_class($user)];
        if (!$userSpecificRepository instanceof UserWithResettablePasswordProvider) {
            throw new \InvalidArgumentException(sprintf('$user can only be updated by object implementing the "%s" interface', UserWithResettablePasswordProvider::class));
        }

        $userSpecificRepository->updatePassword($user, $encodedPassword, $salt);
    }

    /**
     * Update the city of the given user
     * @param BaseUser $user
     * @param City $newCity
     */
    public function updateUserCity(BaseUser $user, City $newCity)
    {
        $user->getUserProfile()->setCity($newCity);
        $this->userRepository->update($user);
    }

    /**
     * Update the orientation of the given user
     * @param PersonalUser $personalUser
     * @param PersonalOrientationChoices $personalOrientationChoices
     */
    public function updateUserOrientation(PersonalUser $personalUser, PersonalOrientationChoices $personalOrientationChoices)
    {
        $personalUser->setOrientation($personalOrientationChoices);
        $this->userRepository->update($personalUser);
    }

    /**
     * Retrieve PersonalProject from the search result
     * @param Result $searchResult
     * @return array
     */
    public function getPersonalProjectsBySearchResult(Result $searchResult): array
    {
        $result = array();
        $result['totalHits'] = $searchResult->totalHits();
        $result['hits'] = array();
        $ids = array();
        foreach ($searchResult->hits() as $project) {
            $ids[] = $project['id'];
        }
        if (count($ids) > 0) {
            $result['hits'] = $this->projectRepository->findByIds($ids);
        }
        return $result;
    }

    /**
     * Retrieve PersonalVehicle, ProVehicle and PersonalProject from search items
     * @param ResultSet $searchResult
     * @return array
     */
    public function getMixedBySearchItemResult(ResultSet $searchResult): array
    {
        $results = array();
        $results['totalHits'] = $searchResult->getTotalHits();
        $results['hits'] = array();
        foreach ($searchResult->getResults() as $result) {
            $resultData = $result->getData();
            if (strpos($result->getIndex(), IndexableSearchItem::TYPE) === 0) {
                if (isset($resultData['vehicle'])) {
                    if ($resultData['vehicle']['type'] === PersonalVehicle::TYPE) {
                        $results['hits'][] = $this->personalVehicleRepository->find($resultData['id']);
                    } else {
                        $results['hits'][] = $this->proVehicleRepository->find($resultData['id']);
                    }
                } elseif (isset($resultData['project'])) {
                    $results['hits'][] = $this->projectRepository->find($resultData['id']);
                }
            } elseif (strpos($result->getIndex(), IndexablePersonalProject::TYPE) === 0) {
                $results['hits'][] = $this->projectRepository->find($resultData['id']);
            }
        }
        return $results;
    }

    /**
     * @param BaseUSer $user
     * @param UserPreferencesDTO $userPreferencesDTO
     */
    public function editPreferences(BaseUser $user, UserPreferencesDTO $userPreferencesDTO)
    {
        $user->updatePreferences(
            $userPreferencesDTO->getGlobalEmailFrequency(),
            $userPreferencesDTO->isPrivateMessageEmailEnabled(),
            $userPreferencesDTO->isLikeEmailEnabled(),
            $userPreferencesDTO->getPrivateMessageEmailFrequency(),
            $userPreferencesDTO->getLikeEmailFrequency(),
            $userPreferencesDTO->isLeadEmailEnabled(),
            $userPreferencesDTO->isLeadOnlyPartExchange(),
            $userPreferencesDTO->isLeadOnlyProject(),
            $userPreferencesDTO->isLeadProjectWithPartExchange(),
            $userPreferencesDTO->getLeadLocalizationRadiusCriteria(),
            $userPreferencesDTO->getLeadPartExchangeKmMaxCriteria(),
            $userPreferencesDTO->getLeadProjectBudgetMinCriteria()
        );

        $this->userPreferencesRepository->update($user->getPreferences());
    }

    /**
     * Retrieve User from the search result
     * @param Result $searchResult
     * @return array
     */
    public function getUsersBySearchResult(ResultSet $searchResult): array
    {
        $results = array();
        $results['totalHits'] = $searchResult->getTotalHits();
        $results['hits'] = array();
        $ids = array();
        foreach ($searchResult->getResults() as $result) {
            $proUser = $result->getData();
            $ids[] = $proUser ['id'];
        }
        if (count($ids) > 0) {
            $results['hits'] = $this->userRepository->findByIds($ids);
        }
        return $results;
    }

    /**
     * @param PersonalUser $personalUser
     * @param ApplicationUser $currentUser
     * @return array ['errorMessages' => [], 'proUser' = ProUser (result) ]
     */
    public function convertPersonalToProUser(PersonalUser $personalUser, ApplicationUser $currentUser): array
    {
        $result = ['errorMessages' => [], 'proUser' => null];
        $registrationDTO = new RegistrationDTO(ProUser::TYPE);
        $registrationDTO->email = $personalUser->getEmail();
        $personalUser->setEmail('converted.' . $personalUser->getEmail());
        $this->userRepository->update($personalUser);
        $registrationDTO->firstName = $personalUser->getFirstName();
        $registrationDTO->lastName = $personalUser->getLastName();
        $registrationDTO->password = uniqid('pwd');

        $proUser = $this->userRegistrationService->registerUser($registrationDTO, false);
        if ($proUser instanceof ProUser) {
            $proUser->setAvatar($personalUser->getAvatar());
            if ($personalUser->getAvatar() != null) {
                $personalUser->getAvatar()->setUser($proUser);
            }
            $personalUser->setAvatar(null);

            $proUser->getUserProfile()->setTitle($personalUser->getTitle());
            $proUser->getUserProfile()->setDescription($personalUser->getDescription());
            $proUser->getUserProfile()->setPhone($personalUser->getPhone());

            $proUser->setFacebookId($personalUser->getFacebookId());
            $proUser->setFacebookAccessToken($personalUser->getFacebookAccessToken());
            $proUser->setGoogleId($personalUser->getGoogleId());
            $proUser->setGoogleAccessToken($personalUser->getGoogleAccessToken());
            $proUser->setTwitterId($personalUser->getTwitterId());
            $proUser->setTwitterAccessToken($personalUser->getTwitterAccessToken());
            $proUser->setLinkedinId($personalUser->getLinkedinId());
            $proUser->setLinkedinAccessToken($personalUser->getLinkedinAccessToken());

            $proUser->setFirstContactPreference($personalUser->getFirstContactPreference());

            $proUser->updatePreferences(
                $personalUser->getPreferences()->getGlobalEmailFrequency(),
                $personalUser->getPreferences()->isPrivateMessageEmailEnabled(),
                $personalUser->getPreferences()->isLikeEmailEnabled(),
                $personalUser->getPreferences()->getPrivateMessageEmailFrequency(),
                $personalUser->getPreferences()->getLikeEmailFrequency()
            );

            /** @var BaseLikeVehicle $like */
            foreach ($personalUser->getLikes() as $like) {
                $like->setUser($proUser);
                $proUser->addLike($like);
            }
            $personalUser->getLikes()->clear();

            /** @var ConversationUser $conversationUser */
            foreach ($personalUser->getConversationUsers() as $conversationUser) {
                $conversationUser->setUser($proUser);
                $proUser->getConversationUsers()->add($conversationUser);
            }
            $personalUser->getConversationUsers()->clear();

            /** @var Message $message */
            foreach ($personalUser->getMessages() as $message) {
                $message->setUser($proUser);
                $proUser->getMessages()->add($message);
            }
            $personalUser->getMessages()->clear();

            $this->userRepository->update($personalUser);
            $this->userRepository->update($proUser);
            $result['proUser'] = $proUser;

            // Suppression du PersonalUser
            $deletePersonalUserResult = $this->deleteUser($personalUser, $currentUser);

            if (count($deletePersonalUserResult['errorMessages']) > 0) {
                $result['errorMessages'] = array_merge($result['errorMessages'], $deletePersonalUserResult['errorMessages']);
            }
        } else {
            $result['errorMessages'][] = "registerUser() n'a pas créé un ProUser";
        }

        return $result;
    }

    /**
     * Get users who have unread notifications or messages during the last 24h, in order to send them an email according to their preferences
     *
     * @return array
     * @throws \Exception when the interval_spec cannot be parsed as an interval.
     */
    public function getUserWithEmailableUnreadNotifications(int $sinceLastHours = 24)
    {
        return $this->userRepository->getUsersWithWaitingNotificationsOrMessages($sinceLastHours);
    }

    /**
     * @param BaseUser $user
     * @param ProUser $userToggle
     * @return bool false if expert is removed, true if expert is added
     */
    public function toggleExpert(BaseUser $user, ProUser $userToggle)
    {
        if ($user->hasExpert($userToggle)) {
            $user->removeExpert($userToggle);
            $this->userRepository->update($user);
            return false;
        } else {
            $user->addExpert($userToggle);
            $this->userRepository->update($user);
            return true;
        }
    }

    /**
     * Update ProServices of a ProUser with the given $proServices
     * @param ProUser $proUser
     * @param array $proServices List of selected ProServices
     */
    public function updateProServicesOfUser(ProUser $proUser, array $proServices)
    {
        $proUserServices = $proUser->getProUserServices();
        $proServicesToDelete = array_diff($proUserServices, $proServices);

        $proUserProServicesToDelete = [];
        /** @var ProUserProService $proUserProService */
        foreach ($proUser->getProUserProServices() as $proUserProService) {
            if (in_array($proUserProService->getProService(), $proServicesToDelete)) {
                $proUserProServicesToDelete[] = $proUserProService;
            }

        }
        foreach ($proServices as $service) {
            if (!in_array($service, $proUserServices)) {
                $newProUserProService = new ProUserProService();
                $newProUserProService->setProUser($proUser);
                $newProUserProService->setProService($service);
                $proUser->addProUserProService($newProUserProService);
            }
        }

        $this->proUserProServiceRepository->removeBulk($proUserProServicesToDelete);
        $this->userRepository->update($proUser);
        $this->eventBus->handle(new ProUserUpdated($proUser));
    }

    /**
     * @param ProUser $proUser
     * @param ProUserProSpecialitiesDTO $proUserProSpecialitiesDTO
     * @return ProUser
     */
    public function updateProUserSpecialities(ProUser $proUser, ProUserProSpecialitiesDTO $proUserProSpecialitiesDTO): ProUser
    {
        /** @var ProUserProService $proUserProService */
        foreach ($proUser->getProUserProServices() as $proUserProService) {
            if (isset($proUserProSpecialitiesDTO->getProUserProServicesForSpecialities()[$proUserProService->getId()])) {
                $proUserProService->setIsSpeciality($proUserProSpecialitiesDTO->getProUserProServicesForSpecialities()[$proUserProService->getId()]->isSpeciality());
            } else {
                $proUserProService->setIsSpeciality(false);
            }
        }

        $this->userRepository->update($proUser);
        $this->eventBus->handle(new ProUserUpdated($proUser));

        return $proUser;
    }

    /**
     * Delete a service offered by a pro
     * @param ProUserProService $proUserProService
     */
    public function deleteProUserProService(ProUserProService $proUserProService)
    {
        $proUser = $proUserProService->getProUser();
        $this->proUserProServiceRepository->remove($proUserProService);
        $this->eventBus->handle(new ProUserUpdated($proUser));
    }

}
