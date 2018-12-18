<?php

namespace Wamcar\User;

use AppBundle\Doctrine\Entity\AffinityDegree;
use AppBundle\Doctrine\Entity\UserPicture;
use AppBundle\Doctrine\Entity\UserPreferences;
use AppBundle\Security\SecurityInterface\HasApiCredential;
use AppBundle\Security\SecurityTrait\ApiCredentialTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Symfony\Component\HttpFoundation\File\File;
use TypeForm\Doctrine\Entity\AffinityAnswer;
use Wamcar\Location\City;
use Wamcar\User\Enum\FirstContactPreference;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\Enum\NotificationFrequency;

abstract class BaseUser implements HasApiCredential
{
    use ApiCredentialTrait;
    use SoftDeleteable;

    const TYPE = '';

    /** @var int */
    protected $id;
    /** @var string */
    protected $email;
    /** @var  UserProfile|null */
    protected $userProfile;
    /** @var null|Picture */
    protected $avatar;
    /** @var  Collection <Message> */
    protected $messages;
    /** @var  Collection <ConversationUser> */
    protected $conversationUsers;
    /** @var string */
    protected $facebookId;
    /** @var string */
    protected $facebookAccessToken;
    /** @var string */
    protected $linkedinId;
    /** @var string */
    protected $linkedinAccessToken;
    /** @var string */
    protected $googleId;
    /** @var string */
    protected $googleAccessToken;
    /** @var string */
    protected $twitterId;
    /** @var string */
    protected $twitterAccessToken;
    /** @var ?FirstContactPreference */
    protected $firstContactPreference;
    /** @var Collection */
    protected $likes;
    /** @var UserPreferences */
    protected $preferences;
    /** @var AffinityAnswer|null */
    protected $affinityAnswer;
    /** @®var Collection $affinityDegree AffinityDegree with smaller-id-user */
    protected $greaterIdUserAffinityDegrees;
    /** @®var Collection $affinityDegree AffinityDegree with greater-id-user */
    protected $smallerIdUserAffinityDegrees;

    /**
     * User constructor.
     * @param string $email
     * @param string $firstName
     * @param string|null $name
     * @param Picture|null $avatar
     * @param City|null $city
     */
    public function __construct(
        string $email,
        string $firstName,
        string $name = null,
        Picture $avatar = null,
        City $city = null
    )
    {
        $this->email = $email;
        $this->userProfile = new UserProfile(null, $firstName, $name, null, null, $city);
        $this->avatar = $avatar;
        $this->messages = new ArrayCollection();
        $this->conversationUsers = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->preferences = new UserPreferences($this);
        $this->greaterIdUserAffinityDegrees = new ArrayCollection();
        $this->smallerIdUserAffinityDegrees = new ArrayCollection();
        $this->generateApiCredentials();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFullName(bool $restrictedName = false): ?string
    {
        if ($restrictedName) {
            return $this->getFirstName();
        } else {
            return $this->getFirstName() . ($this->getLastName() ? ' ' . $this->getLastName() : '');
        }
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return (null !== $this->getUserProfile() ? $this->getUserProfile()->getFirstName() : null);
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return (null !== $this->getUserProfile() ? $this->getUserProfile()->getLastName() : null);
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return (null !== $this->getUserProfile() ? $this->getUserProfile()->getDescription() : null);
    }


    /**
     * @return Title|null
     */
    public function getTitle(): ?Title
    {
        return (null !== $this->getUserProfile() ? $this->getUserProfile()->getTitle() : null);
    }

    /**
     * @return string
     */
    public function getPhone(): ?string
    {
        return (null !== $this->getUserProfile() ? $this->getUserProfile()->getPhone() : null);
    }

    /**
     * @return string
     */
    public function getCity(): ?City
    {
        return (null !== $this->getUserProfile() && null !== $this->getUserProfile()->getCity() && !$this->getUserProfile()->getCity()->isEmpty() ? $this->getUserProfile()->getCity() : null);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return UserProfile
     */
    public function getUserProfile(): ?UserProfile
    {
        return $this->userProfile;
    }

    /**
     * @param null|UserProfile $userProfile
     */
    public function updateUserProfile($userProfile)
    {
        $this->userProfile = $userProfile;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return static::TYPE;
    }

    /**
     * @return string|null
     */
    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    /**
     * @param string|null $facebookId
     */
    public function setFacebookId(?string $facebookId): void
    {
        $this->facebookId = $facebookId;
    }

    /**
     * @return string|null
     */
    public function getFacebookAccessToken(): ?string
    {
        return $this->facebookAccessToken;
    }

    /**
     * @param string|null $facebookAccessToken
     */
    public function setFacebookAccessToken(?string $facebookAccessToken): void
    {
        $this->facebookAccessToken = $facebookAccessToken;
    }

    /**
     * @return string|null
     */
    public function getLinkedinId(): ?string
    {
        return $this->linkedinId;
    }

    /**
     * @param string|null $linkedinId
     */
    public function setLinkedinId(?string $linkedinId): void
    {
        $this->linkedinId = $linkedinId;
    }

    /**
     * @return string|null
     */
    public function getLinkedinAccessToken(): ?string
    {
        return $this->linkedinAccessToken;
    }

    /**
     * @param string|null $linkedinAccessToken
     */
    public function setLinkedinAccessToken(?string $linkedinAccessToken): void
    {
        $this->linkedinAccessToken = $linkedinAccessToken;
    }

    /**
     * @return string|null
     */
    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    /**
     * @param string $googleId
     */
    public function setGoogleId(string $googleId): void
    {
        $this->googleId = $googleId;
    }

    /**
     * @return string|null
     */
    public function getGoogleAccessToken(): ?string
    {
        return $this->googleAccessToken;
    }

    /**
     * @param string|null $googleAccessToken
     */
    public function setGoogleAccessToken(?string $googleAccessToken): void
    {
        $this->googleAccessToken = $googleAccessToken;
    }

    /**
     * @return string|null
     */
    public function getTwitterId(): ?string
    {
        return $this->twitterId;
    }

    /**
     * @param string|null $twitterId
     */
    public function setTwitterId(?string $twitterId): void
    {
        $this->twitterId = $twitterId;
    }

    /**
     * @return string|null
     */
    public function getTwitterAccessToken(): ?string
    {
        return $this->twitterAccessToken;
    }

    /**
     * @param string|null $twitterAccessToken
     */
    public function setTwitterAccessToken(?string $twitterAccessToken): void
    {
        $this->twitterAccessToken = $twitterAccessToken;
    }

    /**
     * @return FirstContactPreference|null
     */
    public function getFirstContactPreference()
    {
        return $this->firstContactPreference;
    }

    /**
     * @param ?FirstContactPreference $firstContactPreference
     */
    public function setFirstContactPreference(?FirstContactPreference $firstContactPreference): void
    {
        $this->firstContactPreference = $firstContactPreference;
    }

    /**
     * @param mixed|null $user
     * @return bool
     */
    public function is($user): bool
    {
        return $user instanceof self && $user->getId() === $this->getId();
    }

    /**
     * @return null|Picture
     */
    public function getAvatar(): ?Picture
    {
        return $this->avatar;
    }

    /**
     * @return null|File
     */
    public function getAvatarFile(): ?File
    {
        return $this->avatar ? $this->avatar->getFile() : null;
    }

    /**
     * @return Collection <Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @return Collection <ConversationUser>
     */
    public function getConversationUsers(): Collection
    {
        return $this->conversationUsers;
    }

    /**
     * @return Collection
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    /**
     * @return array
     */
    public function getPositiveLikes(): array
    {
        $positiveLikes = array();
        /** @var BaseLikeVehicle $like */
        foreach ($this->likes as $like) {
            if ($like->getValue() > 0) {
                $positiveLikes[] = $like;
            }
        }
        return $positiveLikes;
    }

    /**
     * @param BaseVehicle $vehicle
     * @return BaseLikeVehicle|null
     */
    public function getLikeOfVehicle(BaseVehicle $vehicle): BaseLikeVehicle
    {
        /** @var BaseLikeVehicle $like */
        foreach ($this->likes as $like) {
            if ($like->getVehicle() === $vehicle) {
                return $like;
            }
        }
        return null;
    }

    /**
     * @param BaseLikeVehicle $likeVehicle
     */
    public function addLike(BaseLikeVehicle $likeVehicle)
    {
        $this->likes->add($likeVehicle);
    }

    /**
     * @param UserPicture $avatar
     */
    public function setAvatar(?UserPicture $avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @return bool
     */
    public function isPro(): bool
    {
        return $this->getType() === ProUser::TYPE;
    }

    /**
     * @return bool
     */
    public function isPersonal(): bool
    {
        return $this->getType() === PersonalUser::TYPE;
    }

    /**
     * @return UserPreferences|null
     */
    public function getPreferences(): ?UserPreferences
    {
        return $this->preferences;
    }

    public function updatePreferences(
        bool $privateMessageEmailEnabled,
        bool $likeEmailEnabled,
        NotificationFrequency $privateMessageEmailFrequency,
        NotificationFrequency $likeEmailFrequency): void
    {
        $this->getPreferences()->setPrivateMessageEmailEnabled($privateMessageEmailEnabled);
        $this->getPreferences()->setPrivateMessageEmailFrequency($privateMessageEmailFrequency);
        $this->getPreferences()->setLikeEmailEnabled($likeEmailEnabled);
        $this->getPreferences()->setLikeEmailFrequency($likeEmailFrequency);
    }

    /**
     * @return AffinityAnswer|null
     */
    public function getAffinityAnswer(): ?AffinityAnswer
    {
        return $this->affinityAnswer;
    }

    /**
     * @param AffinityAnswer|null $affinityAnswer
     */
    public function setAffinityAnswer(?AffinityAnswer $affinityAnswer): void
    {
        $this->affinityAnswer = $affinityAnswer;
    }

    /**
     * @return mixed
     */
    public function getGreaterIdUserAffinityDegrees()
    {
        return $this->greaterIdUserAffinityDegrees;
    }

    /**
     * @return mixed
     */
    public function getSmallerIdUserAffinityDegrees()
    {
        return $this->smallerIdUserAffinityDegrees;
    }

    /**
     * Return the affinity degree between this user and the given user
     * @param null|BaseUser $withUser The user to get the affinity degree with (can be null in twig template)
     * @return AffinityDegree|null
     */
    public function getAffinityDegreesWith(?BaseUser $withUser): ?AffinityDegree
    {

        if ($withUser === null || $this->is($withUser)) {
            return null;
        }
        if ($this->getId() < $withUser->getId()) {
            $criteria = new Criteria();
            $criteria->where(Criteria::expr()->eq('greaterIdUser', $withUser));
            $result = $this->greaterIdUserAffinityDegrees->matching($criteria);
        } else {
            $criteria = new Criteria();
            $criteria->where(Criteria::expr()->eq('smallerIdUser', $withUser));
            $result = $this->smallerIdUserAffinityDegrees->matching($criteria);
        }
        if ($result->first() === false) {
            return null;
        } else {
            return $result->first();
        }
    }

    /**
     * @param BaseUser|null $user null if user not connected
     * @return bool
     */
    abstract public function canSeeMyVehicles(BaseUser $user = null): bool;

    /**
     * @param null|int $limit
     * @param null|BaseVehicle $excludedVehicle
     * @return Collection
     */
    abstract public function getVehicles(?int $limit = 0, BaseVehicle $excludedVehicle = null): Collection;
}
