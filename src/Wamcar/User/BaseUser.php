<?php

namespace Wamcar\User;

use AppBundle\Doctrine\Entity\UserPicture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Wamcar\Vehicle\BaseVehicle;

abstract class BaseUser
{
    const TYPE = '';

    /** @var int */
    protected $id;
    /** @var string */
    protected $email;
    /** @var  ?UserProfile */
    protected $userProfile;
    /** @var ?Picture */
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
    /** @var Collection */
    protected $likes;

    /**
     * User constructor.
     * @param string $email
     * @param string $firstName
     * @param ?string|null $name
     * @param Picture|null $avatar
     */
    public function __construct(
        string $email,
        string $firstName,
        string $name = null,
        Picture $avatar = null
    )
    {
        $this->email = $email;
        $this->userProfile = new UserProfile(null, $firstName, $name);
        $this->avatar = $avatar;
        $this->messages = new ArrayCollection();
        $this->conversationUsers = new ArrayCollection();
        $this->likes = new ArrayCollection();
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
    public function getFullName(): ?string
    {
        return $this->getFirstName() . ($this->getLastName() ? ' ' . $this->getLastName() : '');
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

    public function getTitle(): ?Title
    {
        return (null !== $this->getUserProfile() ? $this->getUserProfile()->getTitle() : null);
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
     * @return string
     */
    public function getFacebookId(): string
    {
        return $this->facebookId;
    }

    /**
     * @param string $facebookId
     */
    public function setFacebookId(string $facebookId): void
    {
        $this->facebookId = $facebookId;
    }

    /**
     * @return string
     */
    public function getFacebookAccessToken(): string
    {
        return $this->facebookAccessToken;
    }

    /**
     * @param string $facebookAccessToken
     */
    public function setFacebookAccessToken(string $facebookAccessToken): void
    {
        $this->facebookAccessToken = $facebookAccessToken;
    }

    /**
     * @return string
     */
    public function getLinkedinId(): string
    {
        return $this->linkedinId;
    }

    /**
     * @param string $linkedinId
     */
    public function setLinkedinId(string $linkedinId): void
    {
        $this->linkedinId = $linkedinId;
    }

    /**
     * @return string
     */
    public function getLinkedinAccessToken(): string
    {
        return $this->linkedinAccessToken;
    }

    /**
     * @param string $linkedinAccessToken
     */
    public function setLinkedinAccessToken(string $linkedinAccessToken): void
    {
        $this->linkedinAccessToken = $linkedinAccessToken;
    }

    /**
     * @return string
     */
    public function getGoogleId(): string
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
     * @return string
     */
    public function getTwitterId(): string
    {
        return $this->twitterId;
    }

    /**
     * @param string $twitterId
     */
    public function setTwitterId(string $twitterId): void
    {
        $this->twitterId = $twitterId;
    }

    /**
     * @return string
     */
    public function getTwitterAccessToken(): string
    {
        return $this->twitterAccessToken;
    }

    /**
     * @param string $twitterAccessToken
     */
    public function setTwitterAccessToken(string $twitterAccessToken): void
    {
        $this->twitterAccessToken = $twitterAccessToken;
    }

    /**
     * @return string
     */
    public function getGoogleAccessToken(): string
    {
        return $this->googleAccessToken;
    }

    /**
     * @param string $googleAccessToken
     */
    public function setGoogleAccessToken(string $googleAccessToken): void
    {
        $this->googleAccessToken = $googleAccessToken;
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
     * @param BaseUser|null $user null if user not connected
     * @return bool
     */
    abstract public function canSeeMyVehicles(BaseUser $user = null): bool;

    /**
     * @return null|Collection
     */
    abstract public function getVehicles(): ?Collection;
}
