<?php

namespace AppBundle\Form\DTO;


use Wamcar\User\BaseUser;
use Wamcar\Vehicle\Enum\LeadCriteriaSelection;
use Wamcar\Vehicle\Enum\NotificationFrequency;

class UserPreferencesDTO
{
    /** @var NotificationFrequency $globalEmailFrequency */
    private $globalEmailFrequency ;
    /** @var bool $privateMessageEmailEnabled */
    private $privateMessageEmailEnabled;
    /** @var NotificationFrequency $privateMessageEmailFrequency */
    private $privateMessageEmailFrequency;
    /** @var bool $likeEmailEnabled */
    private $likeEmailEnabled;
    /** @var NotificationFrequency $likeEmailFrequency */
    private $likeEmailFrequency;

    // Leads suggestion
    /** @var bool $leadEmailEnabled */
    private $leadEmailEnabled;
    /** @var int $leadLocalizationRadius */
    private $leadLocalizationRadiusCriteria;
    /** @var LeadCriteriaSelection $leadPartExchangeSelectionCriteria */
    private $leadPartExchangeSelectionCriteria;
    /** @var null|int $leadPartExchangeKmMaxCriteria */
    private $leadPartExchangeKmMaxCriteria;
    /** @var LeadCriteriaSelection $leadProjectSelectionCriteria */
    private $leadProjectSelectionCriteria;
    /** @var null|int $leadProjectBudgetMinCriteria */
    private $leadProjectBudgetMinCriteria;


    /**
     * @param BaseUser $user
     * @return UserPreferencesDTO
     */
    public static function createFromUser(BaseUser $user)
    {
        $userPreferencesDTO = new self();

        $userPreferencesDTO->setGlobalEmailFrequency($user->getPreferences()->getGlobalEmailFrequency());

        $userPreferencesDTO->setPrivateMessageEmailEnabled($user->getPreferences()->isPrivateMessageEmailEnabled());
        $userPreferencesDTO->setPrivateMessageEmailFrequency(NotificationFrequency::IMMEDIATELY()/* Désactivé pour la v1 : $user->getPreferences()->getPrivateMessageEmailFrequency()*/);

        $userPreferencesDTO->setLikeEmailEnabled($user->getPreferences()->isLikeEmailEnabled());
        $userPreferencesDTO->setLikeEmailFrequency($user->getPreferences()->getLikeEmailFrequency());

        $userPreferencesDTO->setLeadEmailEnabled($user->getPreferences()->isLeadEmailEnabled());
        $userPreferencesDTO->setLeadLocalizationRadiusCriteria($user->getPreferences()->getLeadLocalizationRadiusCriteria());
        $userPreferencesDTO->setLeadPartExchangeSelectionCriteria($user->getPreferences()->getLeadPartExchangeSelectionCriteria());
        $userPreferencesDTO->setLeadPartExchangeKmMaxCriteria($user->getPreferences()->getLeadPartExchangeKmMaxCriteria());
        $userPreferencesDTO->setLeadProjectSelectionCriteria($user->getPreferences()->getLeadProjectSelectionCriteria());
        $userPreferencesDTO->setLeadProjectBudgetMinCriteria($user->getPreferences()->getLeadProjectBudgetMinCriteria());

        return $userPreferencesDTO;
    }

    /**
     * @return NotificationFrequency
     */
    public function getGlobalEmailFrequency(): NotificationFrequency
    {
        return $this->globalEmailFrequency;
    }

    /**
     * @param NotificationFrequency $globalEmailFrequency
     */
    public function setGlobalEmailFrequency(NotificationFrequency $globalEmailFrequency): void
    {
        $this->globalEmailFrequency = $globalEmailFrequency;
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

    /**
     * @return bool
     */
    public function isLeadEmailEnabled(): bool
    {
        return $this->leadEmailEnabled;
    }

    /**
     * @param bool $leadEmailEnabled
     */
    public function setLeadEmailEnabled(bool $leadEmailEnabled): void
    {
        $this->leadEmailEnabled = $leadEmailEnabled;
    }

    /**
     * @return int
     */
    public function getLeadLocalizationRadiusCriteria(): int
    {
        return $this->leadLocalizationRadiusCriteria;
    }

    /**
     * @param int $leadLocalizationRadiusCriteria
     */
    public function setLeadLocalizationRadiusCriteria(int $leadLocalizationRadiusCriteria): void
    {
        $this->leadLocalizationRadiusCriteria = $leadLocalizationRadiusCriteria;
    }

    /**
     * @return LeadCriteriaSelection
     */
    public function getLeadPartExchangeSelectionCriteria(): LeadCriteriaSelection
    {
        return $this->leadPartExchangeSelectionCriteria;
    }

    /**
     * @param LeadCriteriaSelection $leadPartExchangeSelectionCriteria
     */
    public function setLeadPartExchangeSelectionCriteria(LeadCriteriaSelection $leadPartExchangeSelectionCriteria): void
    {
        $this->leadPartExchangeSelectionCriteria = $leadPartExchangeSelectionCriteria;
    }

    /**
     * @return int|null
     */
    public function getLeadPartExchangeKmMaxCriteria(): ?int
    {
        return $this->leadPartExchangeKmMaxCriteria;
    }

    /**
     * @param int|null $leadPartExchangeKmMaxCriteria
     */
    public function setLeadPartExchangeKmMaxCriteria(?int $leadPartExchangeKmMaxCriteria): void
    {
        $this->leadPartExchangeKmMaxCriteria = $leadPartExchangeKmMaxCriteria;
    }

    /**
     * @return LeadCriteriaSelection
     */
    public function getLeadProjectSelectionCriteria(): LeadCriteriaSelection
    {
        return $this->leadProjectSelectionCriteria;
    }

    /**
     * @param LeadCriteriaSelection $leadProjectSelectionCriteria
     */
    public function setLeadProjectSelectionCriteria(LeadCriteriaSelection $leadProjectSelectionCriteria): void
    {
        $this->leadProjectSelectionCriteria = $leadProjectSelectionCriteria;
    }

    /**
     * @return int|null
     */
    public function getLeadProjectBudgetMinCriteria(): ?int
    {
        return $this->leadProjectBudgetMinCriteria;
    }

    /**
     * @param int|null $leadProjectBudgetMinCriteria
     */
    public function setLeadProjectBudgetMinCriteria(?int $leadProjectBudgetMinCriteria): void
    {
        $this->leadProjectBudgetMinCriteria = $leadProjectBudgetMinCriteria;
    }
}