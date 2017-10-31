<?php

namespace Wamcar\Garage;

class Garage
{
    /** @var int */
    protected $id;
    /** @var string */
    protected $name;
    /** @var  string */
    protected $siren;
    /** @var  string */
    protected $phone;
    /** @var  string */
    protected $email;
    /** @var  string */
    protected $openingHours;
    /** @var  string */
    protected $presentation;
    /** @var  string */
    protected $benefit;
    /** @var  Address */
    protected $address;

    /**
     * Garage constructor.
     * @param string $name
     * @param string $siren
     * @param string $phone
     * @param string $email
     * @param null|string $openingHours
     * @param null|string $presentation
     * @param null|string $benefit
     * @param Address $address
     */
    public function __construct(
        string $name,
        string $siren,
        string $phone,
        string $email,
        ?string $openingHours,
        ?string $presentation,
        ?string $benefit,
        Address $address
    )
    {
        $this->name = $name;
        $this->siren = $siren;
        $this->phone = $phone;
        $this->email = $email;
        $this->openingHours = $openingHours;
        $this->presentation = $presentation;
        $this->benefit = $benefit;
        $this->address = $address;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSiren(): string
    {
        return $this->siren;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getOpeningHours(): ?string
    {
        return $this->openingHours;
    }

    /**
     * @return string
     */
    public function getPresentation(): ?string
    {
        return $this->presentation;
    }

    /**
     * @return string
     */
    public function getBenefit(): ?string
    {
        return $this->benefit;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string $siren
     */
    public function setSiren(string $siren)
    {
        $this->siren = $siren;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone)
    {
        $this->phone = $phone;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @param string $openingHours
     */
    public function setOpeningHours(?string $openingHours)
    {
        $this->openingHours = $openingHours;
    }

    /**
     * @param string $presentation
     */
    public function setPresentation(?string $presentation)
    {
        $this->presentation = $presentation;
    }

    /**
     * @param string $benefit
     */
    public function setBenefit(?string $benefit)
    {
        $this->benefit = $benefit;
    }

    /**
     * @param Address $address
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;
    }
}
