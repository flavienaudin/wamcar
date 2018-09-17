<?php

namespace AppBundle\Doctrine\Entity;


use Wamcar\User\BaseUser;
use Wamcar\Vehicle\Enum\NotificationFrequency;

class UserPreferences
{
    /** @var BaseUser */
    private $user;

    /** @var bool */
    private $privateMessageEmailEnabled;
    /** @var NotificationFrequency */
    private $privateMessageEmailFrequency;
    /** @var bool */
    private $likeEmailEnabled;
    /** @var NotificationFrequency */
    private $likeEmailFrequency;

    /**
     * UserPreferences constructor.
     * @param BaseUser $user
     * @param bool $privateMessageEmailEnabled
     * @param bool $likeEmailEnabled
     * @param null|NotificationFrequency $privateMessageEmailFrequency
     * @param null|NotificationFrequency $likeEmailFrequency
     */
    public function __construct(BaseUser $user, bool $privateMessageEmailEnabled = true, bool $likeEmailEnabled = true, NotificationFrequency $privateMessageEmailFrequency = null, NotificationFrequency $likeEmailFrequency = null)
    {
        $this->user = $user;
        $this->privateMessageEmailEnabled = $privateMessageEmailEnabled;
        $this->privateMessageEmailFrequency = $privateMessageEmailFrequency ?? NotificationFrequency::IMMEDIATELY();
        $this->likeEmailEnabled = $likeEmailEnabled;
        $this->likeEmailFrequency = $likeEmailFrequency ?? NotificationFrequency::ONCE_A_DAY();
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