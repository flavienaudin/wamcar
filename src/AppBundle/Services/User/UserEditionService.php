<?php

namespace AppBundle\Services\User;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\UserPicture;
use AppBundle\Elasticsearch\Type\IndexablePersonalProject;
use AppBundle\Elasticsearch\Type\IndexableSearchItem;
use AppBundle\Form\Builder\User\ProjectFromDTOBuilder;
use AppBundle\Form\DTO\ProjectDTO;
use AppBundle\Form\DTO\ProUserInformationDTO;
use AppBundle\Form\DTO\UserInformationDTO;
use AppBundle\Form\DTO\UserPreferencesDTO;
use AppBundle\Security\HasPasswordResettable;
use AppBundle\Security\Repository\UserWithResettablePasswordProvider;
use AppBundle\Utils\TokenGenerator;
use Elastica\ResultSet;
use Novaway\ElasticsearchClient\Query\Result;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Wamcar\Location\City;
use Wamcar\User\BaseUser;
use Wamcar\User\Enum\PersonalOrientationChoices;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProjectRepository;
use Wamcar\User\UserPreferencesRepository;
use Wamcar\User\UserRepository;
use Wamcar\Vehicle\Enum\NotificationFrequency;
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
    /** @var PersonalVehicleRepository */
    private $personalVehicleRepository;
    /** @var ProVehicleRepository */
    private $proVehicleRepository;
    /** @var UserPreferencesRepository */
    private $userPreferencesRepository;

    /**
     * UserEditionService constructor.
     * @param PasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     * @param array $userSpecificRepositories
     * @param ProjectFromDTOBuilder $projectBuilder
     * @param ProjectRepository $projectRepository
     * @param PersonalVehicleRepository $personalVehicleRepository
     * @param ProVehicleRepository $proVehicleRepository
     * @param UserPreferencesRepository $userPreferencesRepository
     */
    public function __construct(
        PasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        array $userSpecificRepositories,
        ProjectFromDTOBuilder $projectBuilder,
        ProjectRepository $projectRepository,
        PersonalVehicleRepository $personalVehicleRepository,
        ProVehicleRepository $proVehicleRepository,
        UserPreferencesRepository $userPreferencesRepository
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->userSpecificRepositories = $userSpecificRepositories;
        $this->projectBuilder = $projectBuilder;
        $this->projectRepository = $projectRepository;
        $this->personalVehicleRepository = $personalVehicleRepository;
        $this->proVehicleRepository = $proVehicleRepository;
        $this->userPreferencesRepository = $userPreferencesRepository;
    }

    /**
     * @param ApplicationUser $user
     * @param UserInformationDTO $userInformationDTO
     * @return ApplicationUser
     * @throws \Exception
     */
    public function editInformations(ApplicationUser $user, UserInformationDTO $userInformationDTO): ApplicationUser
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

        if ($userInformationDTO instanceof ProUserInformationDTO) {
            $user->setPhonePro($userInformationDTO->phonePro);
        }

        $this->userRepository->update($user);

        return $user;
    }

    /**
     * @param BaseUser $userToDelete
     */
    public function deleteUser(BaseUser $userToDelete, $hardDelete = false){
        $this->userRepository->remove($userToDelete);
        if($hardDelete){
            $this->userRepository->remove($userToDelete);
        }
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
            $userPreferencesDTO->isPrivateMessageEmailEnabled(),
            $userPreferencesDTO->isLikeEmailEnabled(),
            NotificationFrequency::IMMEDIATELY()
            /* TODO Désactivé v1 : $userPreferencesDTO->getPrivateMessageEmailFrequency()*/,
            $userPreferencesDTO->getLikeEmailFrequency()
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
}
