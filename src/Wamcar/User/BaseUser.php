<?php

namespace Wamcar\User;

use AppBundle\Doctrine\Entity\AffinityDegree;
use AppBundle\Doctrine\Entity\UserBanner;
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
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationUser;
use Wamcar\Conversation\Message;
use Wamcar\Location\City;
use Wamcar\User\Enum\FirstContactPreference;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\Enum\NotificationFrequency;

/**
 * Class BaseUser
 * @package Wamcar\User
 */
abstract class BaseUser implements HasApiCredential
{
    use ApiCredentialTrait;
    use SoftDeleteable;

    const TYPE = '';

    /** @var int */
    protected $id;
    /** @var string */
    protected $slug;
    /** @var string */
    protected $email;
    /** @var  UserProfile|null */
    protected $userProfile;
    /** @var null|UserPicture */
    protected $avatar;
    /** @var null|UserBanner */
    protected $banner;
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
    /** @var null|FirstContactPreference */
    protected $firstContactPreference;
    /** @var int */
    protected $creditPoints;
    /** @var string|null */
    protected $youtubeVideoId;
    /** @var string|null */
    protected $videoShortText;
    /** @var string|null */
    protected $videoText;
    /** @var Collection */
    protected $likes;
    /** @var Collection */
    protected $myExperts;
    /** @var Collection */
    protected $expertOf;
    /** @var UserPreferences */
    protected $preferences;
    /** @var null|string */
    protected $deletionReason;
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
     * @param UserPicture|null $avatar
     * @param City|null $city
     * @param UserBanner|null $userBanner
     */
    public function __construct(
        string $email,
        string $firstName,
        string $name = null,
        UserPicture $avatar = null,
        City $city = null,
        UserBanner $userBanner = null
    )
    {
        $this->email = $email;
        $this->userProfile = new UserProfile($firstName, $name, null, null, null, false, $city);
        $this->avatar = $avatar;
        $this->banner = $userBanner;
        $this->creditPoints = 0;
        $this->messages = new ArrayCollection();
        $this->conversationUsers = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->myExperts = new ArrayCollection();
        $this->expertOf = new ArrayCollection();
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
     * @return null|string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param null|string $slug
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
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
     * Set description
     * @param null|string $description
     */
    public function setDescription(?string $description)
    {
        $this->userProfile->setDescription($description);
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
     * @return bool
     */
    public function isPhoneDisplay(): bool
    {
        return (null !== $this->getUserProfile() ? $this->getUserProfile()->isPhoneDisplay() : false);
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
    public function getCityPostalCodeAndName(): string
    {
        $city = $this->getCity();
        return $city != null ? $city->getPostalCode() . ' ' . $city->getName() : '';
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
     * UserID for GoogleAnalyticsTracking
     * @return string
     */
    public function getUserID(): string
    {
        return static::TYPE . '-' . $this->getId();
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
     * @param string|null $googleId
     */
    public function setGoogleId(?string $googleId): void
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
     * @return null|UserPicture
     */
    public function getAvatar(): ?UserPicture
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
     * @return null|UserBanner
     */
    public function getBanner(): ?UserBanner
    {
        return $this->banner;
    }

    /**
     * @return null|File
     */
    public function getBannerFile(): ?File
    {
        return $this->banner ? $this->banner->getFile() : null;
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
     * @param Conversation $searchedConversation
     * @return ConversationUser|null
     */
    public function getConversationUser(Conversation $searchedConversation): ?ConversationUser
    {
        /** @var ConversationUser $conversationUser */
        foreach ($this->conversationUsers as $conversationUser) {
            if ($searchedConversation->getId() === $conversationUser->getConversation()->getId()) {
                return $conversationUser;
            }
        }
        return null;
    }

    public function getTotalMessagesOnConversations(): int
    {
        $total = 0;
        /** @var ConversationUser $conversation */
        foreach ($this->conversationUsers as $conversation) {
            $total += count($conversation->getConversation()->getMessages());
        }
        return $total;
    }


    /**
     * Conversations of the user, which are initiated by the user
     * @return Collection
     */
    public function getInitiatedConversations(): Collection
    {
        return $this->conversationUsers->filter(function (ConversationUser $conversationUser) {
            $conv = $conversationUser->getConversation();
            /** @var Message $firstMessage */
            $firstMessage = $conv->getMessages()->first();

            return $firstMessage->getUser() != null && $this->is($firstMessage->getUser());
        });
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
     * @return Collection|array
     */
    public function getMyExperts(bool $ordered = false)
    {
        if($ordered){
            $orderedExperts = $this->myExperts->toArray();
            uasort($orderedExperts, function ($e1, $e2) {
                $expert1fullname = $e1->getFullname();
                $expert2fullname = $e2->getFullname();

                if ($expert1fullname === $expert2fullname) {
                    return 0;
                }
                return $expert1fullname > $expert2fullname ? 1 : -1;
            });
            return $orderedExperts;
        }else {
            return $this->myExperts;
        }
    }

    /**
     * @param BaseUser $user
     *
     * @return bool
     */
    public function hasExpert(BaseUser $user): bool
    {
        return $this->myExperts->contains($user);
    }

    /**
     * @param BaseUser $expertUser
     */
    public function addExpert(BaseUser $expertUser): void
    {
        $this->myExperts->add($expertUser);
        $expertUser->expertOf->add($this);
    }

    /**
     * @param BaseUser $expertUser
     */
    public function removeExpert(BaseUser $expertUser): void
    {
        $this->myExperts->removeElement($expertUser);
        $expertUser->expertOf->removeElement($this);
    }

    /**
     * @param UserPicture $avatar
     */
    public function setAvatar(?UserPicture $avatar)
    {
        $this->avatar = $avatar;
    }
    /**
     * @param UserBanner $banner
     */
    public function setBanner(?UserBanner $banner)
    {
        $this->banner = $banner;
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
        NotificationFrequency $globalEmailFrequency,
        bool $privateMessageEmailEnabled,
        bool $likeEmailEnabled,
        NotificationFrequency $privateMessageEmailFrequency,
        NotificationFrequency $likeEmailFrequency,
        bool $leadEmailEnabled,
        bool $leadOnlyPartExchange,
        bool $leadOnlyProject,
        bool $leadProjectWithPartExchange,
        int $leadLocalizationRadius,
        ?int $leadPartExchangeKmMax,
        ?int $leadProjectBudgetMin
    ): void
    {
        $this->getPreferences()->setGlobalEmailFrequency($globalEmailFrequency);

        $this->getPreferences()->setPrivateMessageEmailEnabled($privateMessageEmailEnabled);
        // Use global Email Frequency
        $this->getPreferences()->setPrivateMessageEmailFrequency($globalEmailFrequency);

        $this->getPreferences()->setLikeEmailEnabled($likeEmailEnabled);
        // Use global Email Frequency
        $this->getPreferences()->setLikeEmailFrequency($globalEmailFrequency);

        $this->getPreferences()->setLeadEmailEnabled($leadEmailEnabled);
        $this->getPreferences()->setLeadLocalizationRadiusCriteria($leadLocalizationRadius);
        $this->getPreferences()->setLeadOnlyPartExchange($leadOnlyPartExchange);
        $this->getPreferences()->setLeadOnlyProject($leadOnlyProject);
        $this->getPreferences()->setLeadProjectWithPartExchange($leadProjectWithPartExchange);

        $this->getPreferences()->setLeadPartExchangeKmMaxCriteria($leadPartExchangeKmMax);
        $this->getPreferences()->setLeadProjectBudgetMinCriteria($leadProjectBudgetMin);
    }

    /**
     * @return null|string
     */
    public function getDeletionReason(): ?string
    {
        return $this->deletionReason;
    }

    /**
     * @param null|string $deletionReason
     */
    public function setDeletionReason(?string $deletionReason): void
    {
        $this->deletionReason = $deletionReason;
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
     * @return array Array of WamAffinity Degrees ["userId" => value]
     */
    public function getAffinityDegreesAsArray(): array
    {
        $affinityDegreesArray = [];
        /** @var AffinityDegree $affinityDegree */
        foreach ($this->smallerIdUserAffinityDegrees as $affinityDegree) {
            if ($affinityDegree->getSmallerIdUser() != null) {
                $affinityDegreesArray[$affinityDegree->getSmallerIdUser()->getId()] = $affinityDegree->getAffinityValue();
            }
        }
        /** @var AffinityDegree $affinityDegree */
        foreach ($this->greaterIdUserAffinityDegrees as $affinityDegree) {
            if ($affinityDegree->getGreaterIdUser() != null) {
                $affinityDegreesArray[$affinityDegree->getGreaterIdUser()->getId()] = $affinityDegree->getAffinityValue();
            }
        }
        if (empty($affinityDegreesArray)) {
            $affinityDegreesArray[-1] = 0;
        }
        return $affinityDegreesArray;
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

    /**
     * @return int Number of user's vehicles
     */
    public function countVehicles(): int
    {
        return count($this->getVehicles());
    }

    /**
     * @return int
     */
    public function getCreditPoints(): int
    {
        return $this->creditPoints;
    }

    /**
     * @param int $creditPoints
     */
    public function setCreditPoints(int $creditPoints): void
    {
        $this->creditPoints = $creditPoints;
    }

    /**
     * @param int $creditPoints
     * @return int
     */
    public function addCreditPoints(int $creditPoints)
    {
        $this->creditPoints += $creditPoints;
        return $this->creditPoints;
    }

    /**
     * @param int $creditPoints
     * @return int
     */
    public function substractCreditPoints(int $creditPoints)
    {
        $this->creditPoints -= $creditPoints;
        return $this->creditPoints;
    }

    /**
     * @return string|null
     */
    public function getYoutubeVideoId(): ?string
    {
        return $this->youtubeVideoId;
    }

    /**
     * @param string|null $youtubeVideoId
     */
    public function setYoutubeVideoId(?string $youtubeVideoId): void
    {
        $this->youtubeVideoId = $youtubeVideoId;
    }

    /**
     * @return string|null
     */
    public function getVideoShortText(): ?string
    {
        return $this->videoShortText;
    }

    /**
     * @param string|null $videoShortText
     */
    public function setVideoShortText(?string $videoShortText): void
    {
        $this->videoShortText = $videoShortText;
    }

    /**
     * @return string|null
     */
    public function getVideoText(): ?string
    {
        return $this->videoText;
    }

    /**
     * @param string|null $videoText
     */
    public function setVideoText(?string $videoText): void
    {
        $this->videoText = $videoText;
    }
}
