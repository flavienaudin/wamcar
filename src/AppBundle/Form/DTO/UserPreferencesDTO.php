<?php

namespace AppBundle\Form\DTO;


use Wamcar\User\BaseUser;
use Wamcar\Vehicle\Enum\NotificationFrequency;

class UserPreferencesDTO
{
    /** @var bool $privateMessageEmailEnabled */
    public $privateMessageEmailEnabled;
    /** @var NotificationFrequency $privateMessageEmailFrequency */
    public $privateMessageEmailFrequency;
    /** @var bool $likeEmailEnabled */
    public $likeEmailEnabled;
    /** @var NotificationFrequency $likeEmailFrequency */
    public $likeEmailFrequency;


    /**
     * @param BaseUser $user
     * @return UserPreferencesDTO
     */
    public static function createFromUser(BaseUser $user)
    {
        $userPreferencesDTO = new self();

        $userPreferencesDTO->setPrivateMessageEmailEnabled($user->getPreferences()->isPrivateMessageEmailEnabled());
        $userPreferencesDTO->setPrivateMessageEmailFrequency(NotificationFrequency::IMMEDIATELY()/* Désactivé pour la v1 : $user->getPreferences()->getPrivateMessageEmailFrequency()*/);

        $userPreferencesDTO->setLikeEmailEnabled($user->getPreferences()->isLikeEmailEnabled());
        $userPreferencesDTO->setLikeEmailFrequency($user->getPreferences()->getLikeEmailFrequency());

        return $userPreferencesDTO;
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