<?php

namespace AppBundle\Doctrine\Entity;


use Wamcar\User\BaseUser;
use Wamcar\Vehicle\Enum\LeadCriteriaSelection;
use Wamcar\Vehicle\Enum\NotificationFrequency;

class UserPreferences
{
    /** @var BaseUser */
    private $user;

    /** @var NotificationFrequency */
    private $globalEmailFrequency;

    /** @var bool */
    private $privateMessageEmailEnabled;
    /** @var NotificationFrequency */
    private $privateMessageEmailFrequency;
    /** @var bool */
    private $likeEmailEnabled;
    /** @var NotificationFrequency */
    private $likeEmailFrequency;

    // Leads suggestion
    /** @var bool $leadEmailEnabled */
    private $leadEmailEnabled;
    /** @var int $leadLocalizationRadius */
    private $leadLocalizationRadiusCriteria;
    /** @var LeadCriteriaSelection $leadPartExchangeSelectionCriteria */
    private $leadPartExchangeSelectionCriteria;
    /** @var null|int $leadPartExchangeKmCriteria */
    private $leadPartExchangeKmMaxCriteria;
    /** @var LeadCriteriaSelection $leadProjectSelectionCriteria */
    private $leadProjectSelectionCriteria;
    /** @var null|int $leadProjectBudgetMinCriteria */
    private $leadProjectBudgetMinCriteria;

    /**
     * UserPreferences constructor.
     * @param BaseUser $user
     * @param NotificationFrequency|null $globalEmailFrequency
     * @param bool $privateMessageEmailEnabled
     * @param bool $likeEmailEnabled
     * @param NotificationFrequency|null $privateMessageEmailFrequency
     * @param NotificationFrequency|null $likeEmailFrequency
     * @param bool $leadEmailEnabled
     * @param int $leadLocalizationRadiusCriteria
     * @param LeadCriteriaSelection|null $leadPartExchangeSelectionCriteria
     * @param int|null $leadPartExchangeKmMaxCriteria
     * @param LeadCriteriaSelection|null $leadProjectSelectionCriteria
     * @param int|null $leadProjectBudgetMinCriteria
     */
    public function __construct(BaseUser $user,
                                NotificationFrequency $globalEmailFrequency = null,
                                bool $privateMessageEmailEnabled = true,
                                bool $likeEmailEnabled = true,
                                NotificationFrequency $privateMessageEmailFrequency = null,
                                NotificationFrequency $likeEmailFrequency = null,
                                bool $leadEmailEnabled = true,
                                int $leadLocalizationRadiusCriteria = 50,
                                LeadCriteriaSelection $leadPartExchangeSelectionCriteria = null,
                                int $leadPartExchangeKmMaxCriteria = null,
                                LeadCriteriaSelection $leadProjectSelectionCriteria = null,
                                int $leadProjectBudgetMinCriteria = null
    )
    {
        $this->user = $user;
        $this->globalEmailFrequency = $globalEmailFrequency ?? NotificationFrequency::IMMEDIATELY();

        $this->privateMessageEmailEnabled = $privateMessageEmailEnabled;
        $this->privateMessageEmailFrequency = $privateMessageEmailFrequency ?? NotificationFrequency::IMMEDIATELY();

        $this->likeEmailEnabled = $likeEmailEnabled;
        $this->likeEmailFrequency = $likeEmailFrequency ?? NotificationFrequency::ONCE_A_DAY();

        $this->leadEmailEnabled = $leadEmailEnabled;
        $this->leadLocalizationRadiusCriteria = $leadLocalizationRadiusCriteria;
        $this->leadPartExchangeSelectionCriteria = $leadPartExchangeSelectionCriteria ?? LeadCriteriaSelection::LEAD_CRITERIA_NO_MATTER();
        $this->leadPartExchangeKmMaxCriteria = $leadPartExchangeKmMaxCriteria;
        $this->leadProjectSelectionCriteria = $leadProjectSelectionCriteria ?? LeadCriteriaSelection::LEAD_CRITERIA_NO_MATTER();
        $this->leadProjectBudgetMinCriteria = $leadProjectBudgetMinCriteria;
    }

    /**
     * @return BaseUser
     */
    public function getUser(): BaseUser
    {
        return $this->user;
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