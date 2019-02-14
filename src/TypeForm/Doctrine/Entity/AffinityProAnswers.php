<?php

namespace TypeForm\Doctrine\Entity;


class AffinityProAnswers
{
    /** @var AffinityAnswer */
    private $affinityAnswer;
    /** @var string|null */
    private $title;
    /** @var string|null */
    private $mainProfession;
    /** @var string|null */
    private $experience;
    /** @var string|null */
    private $uniform;
    /** @var string|null */
    private $hobby;
    /** @var int|null */
    private $hobbyLevel;
    /** @var string|null (json array) */
    private $advices;
    /** @var string|null (json array) */
    private $vehicleBody;
    /** @var string|null (json array) */
    private $brands;
    /** @var string|null (json array) */
    private $firstContactChannel;
    /** @var string|null */
    private $phoneNumber;
    /** @var string|null (json array) */
    private $availabilities;
    /** @var string|null */
    private $firstContactPref;
    /** @var string|null */
    private $suggestion;
    /** @var string|null (json array) */
    private $prices;
    /** @var string|null (json array) */
    private $otherHobbies;
    /** @var string|null */
    private $road;

    /**
     * AffinityProAnswers constructor.
     * @param AffinityAnswer $affinityAnswer
     */
    public function __construct(AffinityAnswer $affinityAnswer)
    {
        $this->affinityAnswer = $affinityAnswer;
        $this->affinityAnswer->setAffinityProAnswers($this);
    }

    /**
     * @return AffinityAnswer
     */
    public function getAffinityAnswer(): AffinityAnswer
    {
        return $this->affinityAnswer;
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return null|string
     */
    public function getMainProfession(): ?string
    {
        return $this->mainProfession;
    }

    /**
     * @param null|string $mainProfession
     */
    public function setMainProfession(?string $mainProfession): void
    {
        $this->mainProfession = $mainProfession;
    }

    /**
     * @return null|string
     */
    public function getExperience(): ?string
    {
        return $this->experience;
    }

    /**
     * @param null|string $experience
     */
    public function setExperience(?string $experience): void
    {
        $this->experience = $experience;
    }

    /**
     * @return null|string
     */
    public function getUniform(): ?string
    {
        return $this->uniform;
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
    public function getHobby(): ?string
    {
        return $this->hobby;
    }

    /**
     * @param null|string $hobby
     */
    public function setHobby(?string $hobby): void
    {
        $this->hobby = $hobby;
    }

    /**
     * @return int|null
     */
    public function getHobbyLevel(): ?int
    {
        return $this->hobbyLevel;
    }

    /**
     * @param int|null $hobbyLevel
     */
    public function setHobbyLevel(?int $hobbyLevel): void
    {
        $this->hobbyLevel = $hobbyLevel;
    }

    /**
     * @return null|string
     */
    public function getAdvices(): ?string
    {
        return $this->advices;
    }

    /**
     * @return array
     */
    public function getAdvicesAsArray(): array
    {
        return json_decode($this->advices) ?? [];
    }

    /**
     * @param null|string $advices
     */
    public function setAdvices(?string $advices): void
    {
        $this->advices = $advices;
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
    public function getBrands(): ?string
    {
        return $this->brands;
    }

    /**
     * @return array
     */
    public function getBrandsAsArray(): array
    {
        return json_decode($this->brands) ?? [];
    }

    /**
     * @param null|string $brands
     */
    public function setBrands(?string $brands): void
    {
        $this->brands = $brands;
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
    public function getSuggestion(): ?string
    {
        return $this->suggestion;
    }

    /**
     * @param null|string $suggestion
     */
    public function setSuggestion(?string $suggestion): void
    {
        $this->suggestion = $suggestion;
    }

    /**
     * @return null|string
     */
    public function getPrices(): ?string
    {
        return $this->prices;
    }

    /**
     * @return array
     */
    public function getPricesAsArray(): array
    {
        return json_decode($this->prices) ?? [];
    }

    /**
     * @param null|string $prices
     */
    public function setPrices(?string $prices): void
    {
        $this->prices = $prices;
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