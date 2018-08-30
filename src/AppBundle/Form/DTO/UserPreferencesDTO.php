<?php

namespace AppBundle\Form\DTO;


use Wamcar\User\BaseUser;
use Wamcar\Vehicle\Enum\NotificationFrequency;

class UserPreferencesDTO
{
    /** @var BaseUser $user */
    private $user;
    /** @var bool $privateMessageEmailEnabled */
    private $privateMessageEmailEnabled;
    /** @var NotificationFrequency $privateMessageEmailFrequency */
    private $privateMessageEmailFrequency;
    /** @var bool $likeEmailEnabled */
    private $likeEmailEnabled;
    /** @var NotificationFrequency $likeEmailFrequency */
    private $likeEmailFrequency;

    /**
     * UserPreferencesDTO constructor.
     * @param BaseUser $user
     */
    private function __construct(BaseUser $user)
    {
        $this->user = $user;
    }

    /**
     * @param BaseUser $user
     * @return UserPreferencesDTO
     */
    public static function createFromUser(BaseUser $user)
    {
        $userPreferencesDTO = new self($user);
        if ($user->getPreferences() != null) {
            $userPreferencesDTO->setPrivateMessageEmailEnabled($user->getPreferences()->isPrivateMessageEmailEnabled());
            $userPreferencesDTO->setPrivateMessageEmailFrequency($user->getPreferences()->getPrivateMessageEmailFrequency());
            $userPreferencesDTO->setLikeEmailEnabled($user->getPreferences()->isLikeEmailEnabled());
            $userPreferencesDTO->setLikeEmailFrequency($user->getPreferences()->getLikeEmailFrequency());
        }else{
            $userPreferencesDTO->setPrivateMessageEmailEnabled(true);
            $userPreferencesDTO->setPrivateMessageEmailFrequency(NotificationFrequency::ONCE_A_DAY());
            $userPreferencesDTO->setLikeEmailEnabled(true);
            $userPreferencesDTO->setLikeEmailFrequency(NotificationFrequency::ONCE_A_DAY());
        }
        return $userPreferencesDTO;
    }

    /**
     * @return BaseUser
     */
    public function getUser(): BaseUser
    {
        return $this->user;
    }

    /**
     * @return bool
     */
    public function isPrivateMessageEmailEnabled(): bool
    {
        return $this->privateMessageEmailEnabled;
    }

    /**
     * @param bool $privateMessageEmailEnabled
     */
    public function setPrivateMessageEmailEnabled(bool $privateMessageEmailEnabled): void
    {
        $this->privateMessageEmailEnabled = $privateMessageEmailEnabled;
    }

    /**
     * @return NotificationFrequency
     */
    public function getPrivateMessageEmailFrequency(): NotificationFrequency
    {
        return $this->privateMessageEmailFrequency;
    }

    /**
     * @param NotificationFrequency $privateMessageEmailFrequency
     */
    public function setPrivateMessageEmailFrequency(NotificationFrequency $privateMessageEmailFrequency): void
    {
        $this->privateMessageEmailFrequency = $privateMessageEmailFrequency;
    }

    /**
     * @return bool
     */
    public function isLikeEmailEnabled(): bool
    {
        return $this->likeEmailEnabled;
    }

    /**
     * @param bool $likeEmailEnabled
     */
    public function setLikeEmailEnabled(bool $likeEmailEnabled): void
    {
        $this->likeEmailEnabled = $likeEmailEnabled;
    }

    /**
     * @return NotificationFrequency
     */
    public function getLikeEmailFrequency(): NotificationFrequency
    {
        return $this->likeEmailFrequency;
    }

    /**
     * @param NotificationFrequency $likeEmailFrequency
     */
    public function setLikeEmailFrequency(NotificationFrequency $likeEmailFrequency): void
    {
        $this->likeEmailFrequency = $likeEmailFrequency;
    }
}