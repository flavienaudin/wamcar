<?php

namespace Wamcar\Garage;

use AppBundle\Doctrine\Entity\GaragePicture;
use AppBundle\Security\SecurityInterface\HasApiCredential;
use AppBundle\Security\SecurityTrait\ApiCredentialTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\UserInterface;
use Wamcar\Garage\Enum\GarageRole;
use Wamcar\Location\City;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\ProVehicle;

class Garage implements \Serializable, UserInterface, HasApiCredential
{
    use SoftDeleteable;
    use ApiCredentialTrait;

    /** @var int */
    protected $id;
    /** @var string */
    protected $googlePlaceId;
    /** @var string */
    protected $name;
    /** @var  ?string */
    protected $siren;
    /** @var  ?string */
    protected $phone;
    /** @var  string */
    protected $email;
    /** @var  string */
    protected $openingHours;
    /** @var  string */
    protected $presentation;
    /** @var  string */
    protected $benefit;
    /** @var  float */
    protected $googleRating;
    /** @var  Address */
    protected $address;
    /** @var  Collection */
    protected $members;
    /** @var Collection */
    protected $proVehicles;
    /** @var ?Picture */
    protected $banner;
    /** @var ?Picture */
    protected $logo;

    /**
     * Garage constructor.
     * @param string|null $googlePlaceId
     * @param string $name
     * @param string|null $siren
     * @param string|null $openingHours
     * @param string|null $presentation
     * @param Address $address
     * @param string|null $phone
     * @param Picture|null $banner
     * @param Picture|null $logo
     * @param float|null $googleRating
     */
    public function __construct(
        ?string $googlePlaceId,
        string $name,
        ?string $siren,
        ?string $openingHours,
        ?string $presentation,
        Address $address,
        string $phone = null,
        Picture $banner = null,
        Picture $logo = null,
        ?float $googleRating = null
    )
    {
        $this->googlePlaceId = $googlePlaceId;
        $this->name = $name;
        $this->siren = $siren;
        $this->openingHours = $openingHours;
        $this->presentation = $presentation;
        $this->address = $address;
        $this->phone = $phone;
        $this->members = new ArrayCollection();
        $this->proVehicles = new ArrayCollection();
        $this->banner = $banner;
        $this->logo = $logo;
        $this->googleRating = $googleRating;
        $this->generateApiCredentials();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


    /** Méthodes pour l'interface UserInterface */
    public function getUsername()
    {
        return $this->id;
    }

    public function getRoles()
    {
        return array('ROLE_GARAGE');
    }

    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    public function eraseCredentials()
    {
    }
    /** Fin des méthodes pour l'interface UserInterface */


    /** Méthodes pour l'interface Serializable */
    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return serialize(array(
            $this->id,
            $this->name,

        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->name
            ) = unserialize($serialized);
    }
    /** Fin des méthodes pour l'interface Serializable */

    /**
     * @return string|null
     */
    public function getGooglePlaceId(): ?string
    {
        return $this->googlePlaceId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getSiren(): ?string
    {
        return $this->siren;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getOpeningHours(): ?string
    {
        return $this->openingHours;
    }

    /**
     * @return string|null
     */
    public function getPresentation(): ?string
    {
        return $this->presentation;
    }

    /**
     * @return string|null
     */
    public function getBenefit(): ?string
    {
        return $this->benefit;
    }

    /**
     * @return float|null
     */
    public function getGoogleRating(): ?float
    {
        return $this->googleRating;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @return City
     */
    public function getCity(): City
    {
        return $this->address->getCity();
    }

    /**
     * @param string|null $googlePlaceId
     */
    public function setGooglePlaceId(?string $googlePlaceId): void
    {
        $this->googlePlaceId = $googlePlaceId;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param string|null $siren
     */
    public function setSiren(?string $siren)
    {
        $this->siren = $siren;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone)
    {
        $this->phone = $phone;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email)
    {
        $this->email = $email;
    }

    /**
     * @param string|null $openingHours
     */
    public function setOpeningHours(?string $openingHours)
    {
        $this->openingHours = $openingHours;
    }

    /**
     * @param string|null $presentation
     */
    public function setPresentation(?string $presentation)
    {
        $this->presentation = $presentation;
    }

    /**
     * @param string|null $benefit
     */
    public function setBenefit(?string $benefit)
    {
        $this->benefit = $benefit;
    }

    /**
     * @param float|null $googleRating
     */
    public function setGoogleRating(?float $googleRating): void
    {
        $this->googleRating = $googleRating;
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
     * @return Collection
     */
    public function getEnabledMembers(): Collection
    {
        return $this->members->matching(new Criteria(Criteria::expr()->isNull('requestedAt')));
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
    public function hasVehicle(ProVehicle $proVehicle): bool
    {
        /** @var ProVehicle $existProVehicle */
        foreach ($this->getProVehicles() as $existProVehicle) {
            if ($existProVehicle->getId() === $proVehicle->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get garage's administrators
     * @return ProUser[]
     */
    public function getAdministrators(): array
    {
        $garageAdministrators = [];
        /** @var GarageProUser $enabledMember */
        foreach ($this->getEnabledMembers() as $enabledMember) {
            if (GarageRole::GARAGE_ADMINISTRATOR()->equals($enabledMember->getRole())) {
                $garageAdministrators[] = $enabledMember->getProUser();
            }
        }
        return $garageAdministrators;
    }

    /**
     * Get garage's main administrator (the first)
     * @return ProUser
     */
    public function getMainAdministrator(): ProUser
    {
        /** @var GarageProUser $enabledMember */
        foreach ($this->getEnabledMembers() as $enabledMember) {
            if (GarageRole::GARAGE_ADMINISTRATOR()->equals($enabledMember->getRole())) {
                return $enabledMember->getProUser();
            }
        }
        return null;
    }

    /**
     * @return Picture|null
     */
    public function getBanner(): ?Picture
    {
        return $this->banner;
    }

    /**
     * @return File|null
     */
    public function getBannerFile(): ?File
    {
        return $this->banner ? $this->banner->getFile() : null;
    }

    /**
     * @param null|GaragePicture $banner
     */
    public function setBanner(?GaragePicture $banner)
    {
        $this->banner = $banner;
    }

    public function removeBanner()
    {
        if ($this->getBanner()) {
            $this->getBanner()->setGarage(null);
            $this->setBanner(null);
        }
    }

    /**
     * @return Picture|null
     */
    public function getLogo(): ?Picture
    {
        return $this->logo;
    }

    /**
     * @return File|null
     */
    public function getLogoFile(): ?File
    {
        return $this->logo ? $this->logo->getFile() : null;
    }

    /**
     * @param GaragePicture|null $logo
     */
    public function setLogo(?GaragePicture $logo)
    {
        $this->logo = $logo;
    }

    public function removeLogo()
    {
        if ($this->getLogo()) {
            $this->getLogo()->setGarage(null);
            $this->setLogo(null);
        }
    }

}
