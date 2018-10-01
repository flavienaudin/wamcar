<?php

namespace AppBundle\Services\User;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\UserPicture;
use AppBundle\Elasticsearch\Type\IndexablePersonalProject;
use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use AppBundle\Elasticsearch\Type\IndexableProVehicle;
use AppBundle\Form\Builder\User\ProjectFromDTOBuilder;
use AppBundle\Form\DTO\ProjectDTO;
use AppBundle\Form\DTO\ProUserInformationDTO;
use AppBundle\Form\DTO\UserInformationDTO;
use AppBundle\Form\DTO\UserPreferencesDTO;
use AppBundle\Security\HasPasswordResettable;
use AppBundle\Security\Repository\UserWithResettablePasswordProvider;
use AppBundle\Utils\TokenGenerator;
use Novaway\ElasticsearchClient\Query\Result;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Wamcar\Location\City;
use Wamcar\User\BaseUser;
use Wamcar\User\ProjectRepository;
use Wamcar\User\UserPreferencesRepository;
use Wamcar\User\UserRepository;
use Wamcar\Vehicle\Enum\NotificationFrequency;
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
     * Retrieve PersonalVehicle, ProVehicle and PersonalProject from the search result ALL
     * @param Result $searchResult
     * @return array
     */
    public function getMixedBySearchResult(Result $searchResult): array
    {
        $result = array();
        $result['totalHits'] = $searchResult->totalHits();
        $result['hits'] = array();
        foreach ($searchResult->hits() as $hit) {
            if ($hit['type'] === IndexablePersonalVehicle::TYPE) {
                $result['hits'][] = $this->personalVehicleRepository->find($hit['id']);
            } elseif ($hit['type'] === IndexableProVehicle::TYPE) {
                $result['hits'][] = $this->proVehicleRepository->find($hit['id']);
            } elseif ($hit['type'] === IndexablePersonalProject::TYPE) {
                $result['hits'][] = $this->projectRepository->find($hit['id']);
            }
        }
        return $result;
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
    public function getUsersBySearchResult(Result $searchResult): array
    {
        $result = array();
        $result['totalHits'] = $searchResult->totalHits();
        $result['hits'] = array();
        $ids = array();
        foreach ($searchResult->hits() as $project) {
            $ids[] = $project['id'];
        }
        if (count($ids) > 0) {
            $result['hits'] = $this->userRepository->findByIds($ids);
        }
        return $result;
    }
}
