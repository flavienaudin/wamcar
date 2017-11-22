<?php

namespace Wamcar\Garage;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Wamcar\Vehicle\ProVehicle;

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
    /** @var  Collection */
    protected $members;
    /** @var Collection */
    protected $proVehicles;

    /**
     * Garage constructor.
     * @param string $name
     * @param string $siren
     * @param null|string $openingHours
     * @param null|string $presentation
     * @param Address $address
     * @param string $phone
     */
    public function __construct(
        string $name,
        string $siren,
        ?string $openingHours,
        ?string $presentation,
        Address $address,
        string $phone
    )
    {
        $this->name = $name;
        $this->siren = $siren;
        $this->openingHours = $openingHours;
        $this->presentation = $presentation;
        $this->address = $address;
        $this->phone = $phone;
        $this->members = new ArrayCollection();
        $this->proVehicles = new ArrayCollection();
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
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
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
    public function setPhone(?string $phone)
    {
        $this->phone = $phone;
    }

    /**
     * @param string $email
     */
    public function setEmail(?string $email)
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

    /**
     * @return Collection
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    /**
     * @param Collection $members
     */
    public function setMembers(Collection $members)
    {
        $this->members = $members;
    }

    /**
     * @param GarageProUser $member
     * @return Garage
     */
    public function addMember(GarageProUser $member): Garage
    {
        $this->getMembers()->add($member);

        return $this;
    }

    /**
     * @param GarageProUser $member
     * @return Garage
     */
    public function removeMember(GarageProUser $member): Garage
    {
        $this->members->removeElement($member);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getProVehicles(): Collection
    {
        return $this->proVehicles;
    }

    /**
     * @param ProVehicle $proVehicle
     * @return Garage
     */
    public function addProVehicle(ProVehicle $proVehicle): Garage
    {
        $this->getProVehicles()->add($proVehicle);

        return $this;
    }

    /**
     * @param ProVehicle $proVehicle
     * @return Garage
     */
    public function removeProVehicle(ProVehicle $proVehicle): Garage
    {
        $this->proVehicles->removeElement($proVehicle);

        return $this;
    }

    /**
     * @param ProVehicle $proVehicle
     * @return bool
     */
    public function isProVehicle(ProVehicle $proVehicle): bool
    {
        /** @var ProVehicle $existProVehicle */
        foreach ($this->getProVehicles() as $existProVehicle) {
            if ($existProVehicle->getId() === $proVehicle->getId()) {
                return true;
            }
        }

        return false;
    }
}
