<?php


namespace Wamcar\User;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Wamcar\Conversation\ProContactMessage;
use Wamcar\Garage\GarageProUser;
use Wamcar\Location\City;
use Wamcar\Sale\Declaration;
use Wamcar\VideoCoaching\VideoProjectViewer;
use Wamcar\VideoCoaching\VideoProjectMessage;

class ProUser extends BaseUser
{
    const TYPE = 'pro';

    /** @var null|\DateTime */
    protected $publishedAt;
    /** @var null|\DateTime */
    protected $unpublishedAt;
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
    protected $videoModuleAccess;
    /** @var Collection of VideoProjectViewer accessible (creator/follower) by the user */
    protected $videoProjects;
    /** @var Collection of VideoProjectMessage */
    protected $videoProjectMessages;

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
        $this->leads = new ArrayCollection();
        $this->saleDeclarations = new ArrayCollection();
        $this->proContactMessages = new ArrayCollection();
        $this->proUserProServices = new ArrayCollection();
        $this->landingPosition = null;
        $this->videoModuleAccess = false;
        $this->videoProjects = new ArrayCollection();
        $this->videoProjectMessages = new ArrayCollection();
    }

    /**
     * @return \DateTime|null
     */
    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @param \DateTime|null $publishedAt
     */
    public function setPublishedAt(?\DateTime $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getUnpublishedAt(): ?\DateTime
    {
        return $this->unpublishedAt;
    }

    /**
     * @param \DateTime|null $unpublishedAt
     */
    public function setUnpublishedAt(?\DateTime $unpublishedAt): void
    {
        $this->unpublishedAt = $unpublishedAt;
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
     * Tell if the $user is authorized to see $this's profile
     * @param BaseUser|null $user
     * @return bool
     */
    public function canSeeMyProfile(?BaseUser $user): bool
    {
        return true;
    }

    /**
     * Indique si les informations obligatoires sont renseignées
     * @return bool
     */
    public function isRequiredInformationFilled(): bool
    {
        return $this->hasGarage()
            && count($this->getProUserServices(false, true)) >= 2
            && $this->getAvatar() != null;
    }

    /**
     * Indique si le profil est publiable. Conditions actuelles :
     * - Informations obligatoires renseeignées
     *
     * Modèle B2B : toujours publié
     *
     * @return bool
     */
    public function isPublishable(): bool
    {
        return true; /*$this->isRequiredInformationFilled();*/
    }

    /**
     * Information sur remplissage du profil :
     *  - missing_items : array, éléments manquants
     *  - required_nb : int, nombre d'éléments requis
     * @return array
     */
    public function getProfileFillingData(): array
    {
        $missingItems = [];
        if (!$this->hasGarage()) {
            $missingItems[] = 'user.profile.publish.required.garage';
        }
        if (count($this->getProUserServices(false, true)) < 2) {
            $missingItems[] = 'user.profile.publish.required.service';
        }
        if ($this->getAvatar() == null) {
            $missingItems[] = 'user.profile.publish.required.avatar';
        }
        return ['missing_items' => $missingItems, 'required_nb' => 3];
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

    /**
     * @return bool
     */
    public function hasVideoModuleAccess(): bool
    {
        return $this->videoModuleAccess;
    }

    /**
     * @param bool $videoModuleAccess
     */
    public function setVideoModuleAccess(bool $videoModuleAccess): void
    {
        $this->videoModuleAccess = $videoModuleAccess;
    }

    /**
     * @return Collection of VideoProjectViewer
     */
    public function getVideoProjects(): Collection
    {
        return $this->videoProjects;
    }

    /**
     * @return Collection of VideoProjectViewer
     */
    public function getFollowedVideoProjects(): Collection
    {
        return $this->videoProjects->filter(function (VideoProjectViewer $videoProjectViewer) {
            return !$videoProjectViewer->isCreator();
        });
    }

    /**
     * @return Collection of VideoProjectViewer
     */
    public function getCreatedVideoProjects(): Collection
    {
        return $this->videoProjects->filter(function (VideoProjectViewer $videoProjectViewer) {
            return $videoProjectViewer->isCreator();
        });
    }

    /**
     * @param VideoProjectViewer $videoProjectViewer
     */
    public function addVideoProject(VideoProjectViewer $videoProjectViewer)
    {
        $this->videoProjects->add($videoProjectViewer);
    }

    /**
     *
     * @param VideoProjectViewer $videoProjectViewer
     */
    public function removeVideoProject(VideoProjectViewer $videoProjectViewer)
    {
        $this->videoProjects->removeElement($videoProjectViewer);
    }


    /**
     * @return Collection
     */
    public function getVideoProjectMessages(): Collection
    {
        return $this->videoProjectMessages;
    }

    /**
     * @param VideoProjectMessage $videoProjectMessage
     */
    public function addVideoProjectMessages(VideoProjectMessage $videoProjectMessage)
    {
        $this->videoProjectMessages->add($videoProjectMessage);
    }

    /**
     *
     * @param VideoProjectMessage $videoProjectMessage
     */
    public function removeVideoProjectMessages(VideoProjectMessage $videoProjectMessage)
    {
        $this->videoProjectMessages->removeElement($videoProjectMessage);
    }
}
