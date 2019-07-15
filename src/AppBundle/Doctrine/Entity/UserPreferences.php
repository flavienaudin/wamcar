<?php

namespace AppBundle\Doctrine\Entity;


use Wamcar\User\BaseUser;
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
    /** @var bool Reprise sèche */
    private $leadOnlyPartExchange;
    /** @var bool Achat */
    private $leadOnlyProject;
    /** @var bool Achat avec reprise */
    private $leadProjectWithPartExchange;
    /** @var int $leadLocalizationRadius */
    private $leadLocalizationRadiusCriteria;
    /** @var null|int $leadPartExchangeKmCriteria */
    private $leadPartExchangeKmMaxCriteria;
    /** @var null|int $leadProjectBudgetMinCriteria */
    private $leadProjectBudgetMinCriteria;

    /**
     * UserPreferences constructor.
     * @param BaseUser $user
     * @param NotificationFrequency|null $globalEmailFrequency
     * @param bool $privateMessageEmailEnabled
     * @param bool $likeEmailEnabled
     * @param bool $leadEmailEnabled
     * @param bool $leadOnlyPartExchange
     * @param bool $leadOnlyProject
     * @param bool $leadProjectWithPartExchange
     * @param NotificationFrequency|null $privateMessageEmailFrequency
     * @param NotificationFrequency|null $likeEmailFrequency
     * @param int $leadLocalizationRadiusCriteria
     * @param int|null $leadPartExchangeKmMaxCriteria
     * @param int|null $leadProjectBudgetMinCriteria
     */
    public function __construct(BaseUser $user,
                                NotificationFrequency $globalEmailFrequency = null,
                                bool $privateMessageEmailEnabled = true,
                                bool $likeEmailEnabled = true,
                                bool $leadEmailEnabled = true,
                                bool $leadOnlyPartExchange = true,
                                bool $leadOnlyProject = true,
                                bool $leadProjectWithPartExchange = true,
                                NotificationFrequency $privateMessageEmailFrequency = null,
                                NotificationFrequency $likeEmailFrequency = null,
                                int $leadLocalizationRadiusCriteria = 50,
                                int $leadPartExchangeKmMaxCriteria = null,
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
        $this->leadOnlyPartExchange = $leadOnlyPartExchange;
        $this->leadOnlyProject = $leadOnlyProject;
        $this->leadProjectWithPartExchange = $leadProjectWithPartExchange;

        $this->leadLocalizationRadiusCriteria = $leadLocalizationRadiusCriteria;
        $this->leadPartExchangeKmMaxCriteria = $leadPartExchangeKmMaxCriteria;

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
     * @return bool
     */
    public function isLeadOnlyPartExchange(): bool
    {
        return $this->leadOnlyPartExchange;
    }

    /**
     * @param bool $leadOnlyPartExchange
     */
    public function setLeadOnlyPartExchange(bool $leadOnlyPartExchange): void
    {
        $this->leadOnlyPartExchange = $leadOnlyPartExchange;
    }

    /**
     * @return bool
     */
    public function isLeadOnlyProject(): bool
    {
        return $this->leadOnlyProject;
    }

    /**
     * @param bool $leadOnlyProject
     */
    public function setLeadOnlyProject(bool $leadOnlyProject): void
    {
        $this->leadOnlyProject = $leadOnlyProject;
    }

    /**
     * @return bool
     */
    public function isLeadProjectWithPartExchange(): bool
    {
        return $this->leadProjectWithPartExchange;
    }

    /**
     * @param bool $leadProjectWithPartExchange
     */
    public function setLeadProjectWithPartExchange(bool $leadProjectWithPartExchange): void
    {
        $this->leadProjectWithPartExchange = $leadProjectWithPartExchange;
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