<?php

namespace TypeForm\Doctrine\Entity;


class AffinityPersonalAnswers
{
    /** @var AffinityAnswer */
    private $affinityAnswer;
    /** @var int|null */
    private $budget;
    /** @var string|null (json array) */
    private $searchedAdvices;
    /** @var string|null */
    private $newUsed;
    /** @var string|null */
    private $vehicleUsage;
    /** @var int|null */
    private $vehicleNumber;
    /** @var string|null */
    private $personalCompanyActivity;
    /** @var string|null */
    private $howHelp;
    /** @var string|null (json array) */
    private $generation;
    /** @var string|null (json array) */
    private $vehicleBody;
    /** @var string|null (json array) */
    private $energy;
    /** @var int|null */
    private $seatsNumber;
    /** @var string|null (json array) */
    private $strongPoints;
    /** @var string|null (json array) */
    private $improvements;
    /** @var string|null */
    private $optionsChoice;
    /** @var string|null (json array) */
    private $securityOptions;
    /** @var string|null (json array) */
    private $confortOptions;
    /** @var string|null (json array) */
    private $searchedHobbies;
    /** @var string|null */
    private $searchedTitle;
    /** @var string|null */
    private $searchedExperience;
    /** @var string|null (json array) */
    private $uniform;
    /** @var string|null (json array) */
    private $firstContactChannel;
    /** @var string|null */
    private $phoneNumber;
    /** @var string|null (json array) */
    private $availabilities;
    /** @var string|null */
    private $firstContactPref;
    /** @var string|null (json array) */
    private $otherHobbies;
    /** @var string|null */
    private $road;

    /**
     * AffinityPersonalAnswers constructor.
     * @param AffinityAnswer $affinityAnswer
     */
    public function __construct(AffinityAnswer $affinityAnswer)
    {
        $this->affinityAnswer = $affinityAnswer;
        $this->affinityAnswer->setAffinityPersonalAnswers($this);
    }

    /**
     * @return AffinityAnswer
     */
    public function getAffinityAnswer(): AffinityAnswer
    {
        return $this->affinityAnswer;
    }

    /**
     * @return int|null
     */
    public function getBudget(): ?int
    {
        return $this->budget;
    }

    /**
     * @param int|null $budget
     */
    public function setBudget(?int $budget): void
    {
        $this->budget = $budget;
    }

    /**
     * @return null|string
     */
    public function getSearchedAdvices(): ?string
    {
        return $this->searchedAdvices;
    }

    /**
     * @return array
     */
    public function getSearchedAdvicesAsArray(): array
    {
        return json_decode($this->searchedAdvices) ?? [];
    }

    /**
     * @param null|string $searchedAdvices
     */
    public function setSearchedAdvices(?string $searchedAdvices): void
    {
        $this->searchedAdvices = $searchedAdvices;
    }

    /**
     * @return null|string
     */
    public function getNewUsed(): ?string
    {
        return $this->newUsed;
    }

    /**
     * @param null|string $newUsed
     */
    public function setNewUsed(?string $newUsed): void
    {
        $this->newUsed = $newUsed;
    }

    /**
     * @return null|string
     */
    public function getVehicleUsage(): ?string
    {
        return $this->vehicleUsage;
    }

    /**
     * @param null|string $vehicleUsage
     */
    public function setVehicleUsage(?string $vehicleUsage): void
    {
        $this->vehicleUsage = $vehicleUsage;
    }

    /**
     * @return int|null
     */
    public function getVehicleNumber(): ?int
    {
        return $this->vehicleNumber;
    }

    /**
     * @param int|null $vehicleNumber
     */
    public function setVehicleNumber(?int $vehicleNumber): void
    {
        $this->vehicleNumber = $vehicleNumber;
    }

    /**
     * @return null|string
     */
    public function getPersonalCompanyActivity(): ?string
    {
        return $this->personalCompanyActivity;
    }

    /**
     * @param null|string $personalCompanyActivity
     */
    public function setPersonalCompanyActivity(?string $personalCompanyActivity): void
    {
        $this->personalCompanyActivity = $personalCompanyActivity;
    }

    /**
     * @return null|string
     */
    public function getHowHelp(): ?string
    {
        return $this->howHelp;
    }

    /**
     * @param null|string $howHelp
     */
    public function setHowHelp(?string $howHelp): void
    {
        $this->howHelp = $howHelp;
    }

    /**
     * @return null|string
     */
    public function getGeneration(): ?string
    {
        return $this->generation;
    }

    /**
     * @param null|string $generation
     */
    public function setGeneration(?string $generation): void
    {
        $this->generation = $generation;
    }

    /**
     * @return null|string
     */
    public function getVehicleBody(): ?string
    {
        return $this->vehicleBody;
    }

    /**
     * @return array
     */
    public function getVehicleBodyAsArray(): array
    {
        return json_decode($this->vehicleBody) ?? [];
    }

    /**
     * @param null|string $vehicleBody
     */
    public function setVehicleBody(?string $vehicleBody): void
    {
        $this->vehicleBody = $vehicleBody;
    }

    /**
     * @return null|string
     */
    public function getEnergy(): ?string
    {
        return $this->energy;
    }

    /**
     * @param null|string $energy
     */
    public function setEnergy(?string $energy): void
    {
        $this->energy = $energy;
    }

    /**
     * @return int|null
     */
    public function getSeatsNumber(): ?int
    {
        return $this->seatsNumber;
    }

    /**
     * @param int|null $seatsNumber
     */
    public function setSeatsNumber(?int $seatsNumber): void
    {
        $this->seatsNumber = $seatsNumber;
    }

    /**
     * @return null|string
     */
    public function getStrongPoints(): ?string
    {
        return $this->strongPoints;
    }

    /**
     * @param null|string $strongPoints
     */
    public function setStrongPoints(?string $strongPoints): void
    {
        $this->strongPoints = $strongPoints;
    }

    /**
     * @return null|string
     */
    public function getImprovements(): ?string
    {
        return $this->improvements;
    }

    /**
     * @param null|string $improvements
     */
    public function setImprovements(?string $improvements): void
    {
        $this->improvements = $improvements;
    }

    /**
     * @return null|string
     */
    public function getOptionsChoice(): ?string
    {
        return $this->optionsChoice;
    }

    /**
     * @param null|string $optionsChoice
     */
    public function setOptionsChoice(?string $optionsChoice): void
    {
        $this->optionsChoice = $optionsChoice;
    }

    /**
     * @return null|string
     */
    public function getSecurityOptions(): ?string
    {
        return $this->securityOptions;
    }

    /**
     * @param null|string $securityOptions
     */
    public function setSecurityOptions(?string $securityOptions): void
    {
        $this->securityOptions = $securityOptions;
    }

    /**
     * @return null|string
     */
    public function getConfortOptions(): ?string
    {
        return $this->confortOptions;
    }

    /**
     * @param null|string $confortOptions
     */
    public function setConfortOptions(?string $confortOptions): void
    {
        $this->confortOptions = $confortOptions;
    }

    /**
     * @return null|string
     */
    public function getSearchedHobbies(): ?string
    {
        return $this->searchedHobbies;
    }

    /**
     * @return array
     */
    public function getSearchedHobbiesAsArray(): array
    {
        return json_decode($this->searchedHobbies) ?? [];
    }

    /**
     * @param null|string $searchedHobbies
     */
    public function setSearchedHobbies(?string $searchedHobbies): void
    {
        $this->searchedHobbies = $searchedHobbies;
    }

    /**
     * @return null|string
     */
    public function getSearchedTitle(): ?string
    {
        return $this->searchedTitle;
    }

    /**
     * @param null|string $searchedTitle
     */
    public function setSearchedTitle(?string $searchedTitle): void
    {
        $this->searchedTitle = $searchedTitle;
    }

    /**
     * @return null|string
     */
    public function getSearchedExperience(): ?string
    {
        return $this->searchedExperience;
    }

    /**
     * @param null|string $searchedExperience
     */
    public function setSearchedExperience(?string $searchedExperience): void
    {
        $this->searchedExperience = $searchedExperience;
    }

    /**
     * @return null|string
     */
    public function getUniform(): ?string
    {
        return $this->uniform;
    }

    /**
     * @return array
     */
    public function getUniformAsArray(): array
    {
        return json_decode($this->uniform) ?? [];
    }

    /**
     * @param null|string $uniform
     */
    public function setUniform(?string $uniform): void
    {
        $this->uniform = $uniform;
    }

    /**
     * @return null|string
     */
    public function getFirstContactChannel(): ?string
    {
        return $this->firstContactChannel;
    }

    /**
     * @return array
     */
    public function getFirstContactChannelAsArray(): array
    {
        return json_decode($this->firstContactChannel) ?? [];
    }

    /**
     * @param null|string $firstContactChannel
     */
    public function setFirstContactChannel(?string $firstContactChannel): void
    {
        $this->firstContactChannel = $firstContactChannel;
    }

    /**
     * @return null|string
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param null|string $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return null|string
     */
    public function getAvailabilities(): ?string
    {
        return $this->availabilities;
    }

    /**
     * @return array
     */
    public function getAvailabilitiesAsArray(): array
    {
        return json_decode($this->availabilities) ?? [];
    }

    /**
     * @param null|string $availabilities
     */
    public function setAvailabilities(?string $availabilities): void
    {
        $this->availabilities = $availabilities;
    }

    /**
     * @return null|string
     */
    public function getFirstContactPref(): ?string
    {
        return $this->firstContactPref;
    }

    /**
     * @param null|string $firstContactPref
     */
    public function setFirstContactPref(?string $firstContactPref): void
    {
        $this->firstContactPref = $firstContactPref;
    }

    /**
     * @return null|string
     */
    public function getOtherHobbies(): ?string
    {
        return $this->otherHobbies;
    }

    /**
     * @return array
     */
    public function getOtherHobbiesAsArray(): array
    {
        return json_decode($this->otherHobbies) ?? [];
    }

    /**
     * @param null|string $otherHobbies
     */
    public function setOtherHobbies(?string $otherHobbies): void
    {
        $this->otherHobbies = $otherHobbies;
    }

    /**
     * @return null|string
     */
    public function getRoad(): ?string
    {
        return $this->road;
    }

    /**
     * @param null|string $road
     */
    public function setRoad(?string $road): void
    {
        $this->road = $road;
    }
}