<?php


namespace Wamcar\User;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Wamcar\Conversation\ProContactMessage;
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
    /** @var Collection */
    protected $proContactMessages;
    /** @var Collection */
    protected $proUserProServices;
    /** @var boolean */
    protected $askForPublication;

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
        $this->proContactMessages = new ArrayCollection();
        $this->proUserProServices = new ArrayCollection();
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
     * @return Collection
     */
    public function getProContactMessages(): Collection
    {
        return $this->proContactMessages;
    }


    /**
     * @param ProContactMessage $proContactMessage
     */
    public function addProContactMessage(ProContactMessage $proContactMessage): void
    {
        $this->proContactMessages->add($proContactMessage);
    }

    /**
     * @param ProContactMessage $proContactMessage
     */
    public function removeProContactMessage(ProContactMessage $proContactMessage): void
    {
        $this->proContactMessages->removeElement($proContactMessage);
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

    /**
     * @return bool
     */
    public function isAskForPublication(): bool
    {
        return $this->askForPublication;
    }

    /**
     * @param bool $askForPublication
     */
    public function setAskForPublication(bool $askForPublication): void
    {
        $this->askForPublication = $askForPublication;
    }


    /**
     * Tell if the profile is filled enough to be published
     * @return bool
     */
    public function isProfileEnoughFilled(): bool
    {
        return $this->hasGarage()
            && count($this->getProUserServices(false, true)) >= 2
            && $this->getAvatar() != null;
    }

    /**
     * Information sur remplissage du profil :
     *  - rate: taux de remplissage
     *  - missing:
     * @return array
     */
    public function getProfileFillingData(): array
    {
        $filled = 0;
        $missings = [];
        if ($this->hasGarage()) {
            $filled++;
        } else {
            $missings[] = 'garage';
        }
        if (count($this->getProUserServices(false, true)) >= 2) {
            $filled++;
        } else {
            $missings[] = 'services (min 2)';
        }
        if ($this->getAvatar() != null) {
            $filled++;
        } else {
            $missings[] = 'photo';
        }
        dump('filled ', $filled);
        return ['missings' => $missings, 'rate' => intval($filled / 3.0 * 100)];
    }

    /**
     * Conditions :
     * - Profile filled enough
     * - User asked for publication
     * @return bool
     */
    public function isPublishable(): bool
    {
        return $this->isProfileEnoughFilled() && $this->askForPublication;
    }

    /**
     * @return Collection
     */
    public function getProUserProServices(): Collection
    {
        return $this->proUserProServices;
    }

    /**
     * Filtered ProServices on isSpeciality
     * @param array $highlightSpecialities
     * @return array|ArrayCollection|Collection
     */
    public function getProUserSpecialities(array $highlightSpecialities = [])
    {
        $specialities = $this->proUserProServices->filter(function (ProUserProService $proUserProService) {
            return $proUserProService->isSpeciality() && $proUserProService->getProService()->getCategory()->isEnabled();
        });
        if (!empty($highlightSpecialities)) {
            $specialities = $specialities->toArray();
            uasort($specialities, function (ProUserProService $ps1, ProUserProService $ps2) use ($highlightSpecialities) {
                if (in_array($ps1->getProService(), $highlightSpecialities)) {
                    return -1;
                } elseif (in_array($ps2->getProService(), $highlightSpecialities)) {
                    return 1;
                }
                return 0;
            });

        }
        return $specialities;
    }

    /**
     * @param bool $excludeSpecialities
     * @param bool $excludeDisabledCategory To exclude ProService which Category is disabled
     * @return array
     */
    public function getProUserServices(bool $excludeSpecialities = false, bool $excludeDisabledCategory = false)
    {
        if ($excludeSpecialities) {
            $criteria = Criteria::create();
            $criteria->where(Criteria::expr()->eq('isSpeciality', false));
            $result = $this->proUserProServices->matching($criteria);
        } else {
            $result = $this->proUserProServices;
        }

        if ($excludeDisabledCategory) {
            $enabledServices = array_filter($result->toArray(), function (ProUserProService $proUserProService) {
                return $proUserProService->getProService()->getCategory()->isEnabled();
            });
        } else {
            $enabledServices = $result->toArray();
        }

        return array_map(function (ProUserProService $proUserProService) {
            return $proUserProService->getProService();
        }, $enabledServices);
    }

    public function getProServicesByCategory()
    {
        $proServicesByCategory = [];
        /** @var ProUserProService $proUserProService */
        foreach ($this->proUserProServices as $proUserProService) {
            $proService = $proUserProService->getProService();
            if ($proService->getCategory()->isEnabled()) {
                if (!isset($proServicesByCategory[$proService->getCategory()->getLabel()])) {
                    $proServicesByCategory[$proService->getCategory()->getLabel()] = [];
                }
                $proServicesByCategory[$proService->getCategory()->getLabel()][$proService->getSlug()] = $proService;
            }
        }

        return $proServicesByCategory;
    }

    /**
     * @param ProUserProService $proUserProService
     */
    public function addProUserProService(ProUserProService $proUserProService)
    {
        $this->proUserProServices->add($proUserProService);
    }

    /**
     * @param ProUserProService $proUserProService
     */
    public function removeProUserProService(ProUserProService $proUserProService)
    {
        $this->proUserProServices->removeElement($proUserProService);
    }

}
