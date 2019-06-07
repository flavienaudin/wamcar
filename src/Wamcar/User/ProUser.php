<?php


namespace Wamcar\User;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;
use Wamcar\Location\City;
use Wamcar\Sale\Declaration;
use Wamcar\Vehicle\BaseVehicle;

class ProUser extends BaseUser
{
    const TYPE = 'pro';

    /** @var  string */
    protected $phonePro;
    /** @var  string|null */
    protected $presentationTitle;
    /** @var  string|null */
    protected $appointmentText;
    /** @var  string|null */
    protected $appointmentAutofillMessage;
    /** @var  Collection */
    protected $garageMemberships;
    /** @var  Collection */
    protected $vehicles;
    /** @var null|int */
    protected $landingPosition;
    /** @var Collection */
    protected $leads;
    /** @var Collection */
    protected $saleDeclarations;

    /**
     * ProUser constructor.
     * @param string $email
     * @param string $firstName
     * @param string|null $name
     * @param City|null $city
     */
    public function __construct($email, $firstName, $name = null, $city = null)
    {
        parent::__construct($email, $firstName, $name, null, $city);
        $this->garageMemberships = new ArrayCollection();
        $this->vehicles = new ArrayCollection();
        $this->leads = new ArrayCollection();
        $this->saleDeclarations = new ArrayCollection();
        $this->landingPosition = null;
    }

    /**
     * Get the main phone number or null if not given
     * @return null|string
     */
    public function getMainPhoneNumber(): ?string
    {
        return $this->phonePro ?? $this->getPhone();
    }

    /**
     * @return string
     */
    public function getPhonePro(): ?string
    {
        return $this->phonePro;
    }

    /**
     * @param string $phonePro
     */
    public function setPhonePro(?string $phonePro)
    {
        $this->phonePro = $phonePro;
    }

    /**
     * @return null|string
     */
    public function getPresentationTitle(): ?string
    {
        return $this->presentationTitle;
    }

    /**
     * @param null|string $presentationTitle
     */
    public function setPresentationTitle(?string $presentationTitle = null): void
    {
        $this->presentationTitle = $presentationTitle;
    }

    /**
     * @return string|null
     */
    public function getAppointmentText(): ?string
    {
        return $this->appointmentText;
    }

    /**
     * @param string|null $appointmentText
     */
    public function setAppointmentText(?string $appointmentText): void
    {
        $this->appointmentText = $appointmentText;
    }

    /**
     * @return string|null
     */
    public function getAppointmentAutofillMessage(): ?string
    {
        return $this->appointmentAutofillMessage;
    }

    /**
     * @param string|null $appointmentAutofillMessage
     */
    public function setAppointmentAutofillMessage(?string $appointmentAutofillMessage): void
    {
        $this->appointmentAutofillMessage = $appointmentAutofillMessage;
    }

    /**
     * @return Collection
     */
    public function getGarageMemberships(): Collection
    {
        return $this->garageMemberships;
    }

    /**
     * @return Collection
     */
    public function getEnabledGarageMemberships(): Collection
    {
        return $this->garageMemberships->matching(new Criteria(Criteria::expr()->isNull('requestedAt')));
    }

    /**
     * @return int
     */
    public function countEnabledGarageMemberships(): int
    {
        return count($this->getEnabledGarageMemberships());
    }

    /**
     * @return bool
     */
    public function isGarageAdminsitrator(): bool
    {
        /** @var GarageProUser $garageMembership */
        foreach ($this->garageMemberships as $garageMembership) {
            if ($garageMembership->isAdministrator()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Collection $members
     */
    public function setGarageMemberships(Collection $members)
    {
        $this->garageMemberships = $members;
    }

    /**
     * Return list of user's garages ordered by GoogleRating
     * @return array
     */
    public function getGaragesOrderByGoogleRating(): array
    {
        $orderedGarages = $this->getEnabledGarageMemberships()->toArray();
        uasort($orderedGarages, function ($gm1, $gm2) {
            $g1gr = $gm1->getGarage()->getGoogleRating() ?? -1;
            $g2gr = $gm2->getGarage()->getGoogleRating() ?? -1;
            if ($g1gr == $g2gr) {
                return 0;
            }
            return $g1gr < $g2gr ? 1 : -1;
        });
        return $orderedGarages;
    }

    /**
     * Get garages of user
     * @return array
     */
    public function getGarages(): array
    {
        return $this->getEnabledGarageMemberships()->map(function (GarageProUser $garageProUser) {
            return $garageProUser->getGarage();
        })->toArray();
    }

    /**
     * @param GarageProUser $member
     * @return ProUser
     */
    public function addGarageMembership(GarageProUser $member): ProUser
    {
        $this->garageMemberships->add($member);

        return $this;
    }

    /**
     * @param GarageProUser $member
     * @return ProUser
     */
    public function removeGarageMembership(GarageProUser $member): ProUser
    {
        $this->garageMemberships->removeElement($member);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasGarage(): bool
    {
        return $this->numberOfGarages() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function numberOfGarages(): int
    {
        return count($this->getEnabledGarageMemberships());
    }

    /**
     * {@inheritdoc}
     */
    public function getVehicles(?int $limit = 0, BaseVehicle $excludedVehicle = null): Collection
    {
        $criteria = Criteria::create();
        if ($excludedVehicle != null) {
            $criteria->where(Criteria::expr()->neq('id', $excludedVehicle->getId()));
        }
        if ($limit > 0) {
            $criteria->setMaxResults($limit);
        }
        return $this->vehicles->matching($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getVehiclesOfGarage(Garage $garage): Collection
    {
        return $this->vehicles->matching(new Criteria(Criteria::expr()->eq('garage', $garage)));
    }

    /**
     * @return int|null
     */
    public function getLandingPosition(): ?int
    {
        return $this->landingPosition;
    }

    /**
     * @param int|null $landingPosition
     */
    public function setLandingPosition(?int $landingPosition): void
    {
        $this->landingPosition = $landingPosition;
    }

    /**
     * @return Collection
     */
    public function getLeads(): Collection
    {
        return $this->leads;
    }

    /**
     * Get the lead of the given $user. Attention si le $user est softdeleted, aucun lead n'est retourné.
     * @param BaseUser $user
     * @return null|Lead
     */
    public function getLeadOfUser(BaseUser $user): ?Lead
    {
        /** @var Lead $lead */
        foreach ($this->getLeads() as $lead) {
            if ($user->is($lead->getUserLead())) {
                return $lead;
            }
        }
        return null;
    }

    /**
     * @param Lead $lead
     */
    public function addLead(Lead $lead): void
    {
        $this->leads->add($lead);
    }

    /**
     * @param Lead $lead
     */
    public function removeLead(Lead $lead): void
    {
        $this->leads->removeElement($lead);
    }

    /**
     * @return Collection
     */
    public function getSaleDeclarations(): Collection
    {
        return $this->saleDeclarations;
    }

    /**
     * @param Declaration $declaration
     */
    public function addSaleDeclaration(Declaration $declaration): void
    {
        $this->saleDeclarations->add($declaration);
    }

    /**
     * @param Declaration $declaration
     */
    public function removeSaleDeclaration(Declaration $declaration): void
    {
        $this->saleDeclarations->removeElement($declaration);
    }

    /**
     * @param BaseUser|null $user null if user not connected
     * @return bool
     */
    public function canSeeMyVehicles(BaseUser $user = null): bool
    {
        return true;
    }

    /**
     * Tell if the $user is authorized to see $this's profile
     * @param BaseUser|null $user
     * @return bool
     */
    public function canSeeMyProfile(?BaseUser $user): bool
    {
        return true;
    }
}
