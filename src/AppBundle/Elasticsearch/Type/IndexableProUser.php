<?php

namespace AppBundle\Elasticsearch\Type;


use AppBundle\Doctrine\Entity\ProApplicationUser;
use Novaway\ElasticsearchClient\Indexable;
use Wamcar\Garage\Enum\GarageRole;
use Wamcar\Garage\GarageProUser;
use Wamcar\User\ProUserProService;

class IndexableProUser implements Indexable
{
    const TYPE = 'pro_user';

    /** @var int $id */
    private $id;
    /** @var string $firstName */
    private $firstName;
    /** @var null|string $lastName */
    private $lastName;
    /** @var null|string $presentationTitle */
    private $presentationTitle;
    /** @var null|string $description */
    private $description;
    /** @var int $descriptionLength */
    private $descriptionLength;
    /** @var array */
    private $garages = [];
    /** @var float */
    private $maxGarageGoogleRating;
    /** @var int */
    private $hasAvatar;
    /** @var (Role|string)[] */
    private $roles;
    /** @var string[] (ProService->name[]) */
    private $proServices;
    /** @var string[] (ProService->name[] if ProUserProService->isSpeciality == true) */
    private $proSpecialities;
    /** @var null|\DateTime */
    private $deletedAt;
    /** @var boolean */
    private $isPublishable;

    /**
     * IndexableProUser constructor.
     * @param int $id
     * @param string $firstName
     * @param null|string $lastName
     * @param null|string $presentationTitle
     * @param null|string $description
     * @param array $garages
     * @param int $hasAvatar
     * @param array $roles
     * @param array $proServices
     * @param array $proSpecialities
     * @param null|\DateTime $deletedAt
     */
    private function __construct(int $id,
                                 string $firstName,
                                 ?string $lastName,
                                 ?string $presentationTitle,
                                 ?string $description,
                                 array $garages,
                                 int $hasAvatar,
                                 array $roles,
                                 array $proServices,
                                 array $proSpecialities,
                                 ?\DateTime $deletedAt)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->presentationTitle = $presentationTitle;
        $this->description = $description;
        $this->descriptionLength = $this->description?strlen($this->description):0;
        $this->garages = $garages;
        $this->hasAvatar = $hasAvatar;
        $this->roles = $roles;
        $this->proServices = $proServices;
        $this->proSpecialities = $proSpecialities;
        $this->deletedAt = $deletedAt;
    }

    public static function createFromProApplicationUser(ProApplicationUser $proApplicationUser): IndexableProUser
    {
        $indexableProUser = new self(
            $proApplicationUser->getId(),
            $proApplicationUser->getFirstName(),
            $proApplicationUser->getLastName(),
            $proApplicationUser->getPresentationTitle(),
            $proApplicationUser->getDescription(),
            [],
            ($proApplicationUser->getAvatar() != null?1:0), // int for function score
            $proApplicationUser->getRoles(),
            array_map(function (ProUserProService $proUserProService) {
                return $proUserProService->getProService()->getName();
            }, $proApplicationUser->getProUserProServices()->toArray()),
            array_map(function (ProUserProService $proUserProService) {
                return $proUserProService->getProService()->getName();
            }, $proApplicationUser->getProUserSpecialities()->toArray()),
            $proApplicationUser->getDeletedAt()
        );
        $indexableProUser->isPublishable = $proApplicationUser->isPublishable();

        $indexableProUser->maxGarageGoogleRating = -1;
        /** @var GarageProUser $garageMembership */
        foreach ($proApplicationUser->getEnabledGarageMemberships() as $garageMembership) {
            $garage = $garageMembership->getGarage();
            if($garage->isOptionAdminVisible() || GarageRole::GARAGE_MEMBER()->equals($garageMembership->getRole())) {
                // Index garage only if pro user is just a member or admin are visibles
                $garageArray = [
                    'garageId' => $garage->getId(),
                    'garageName' => $garage->getName(),
                    'garagePresentation' => $garage->getPresentation(),
                    'garageCityName' => $garage->getCity()->getName(),
                    'garageLocation' => [
                        'lat' => $garage->getCity()->getLatitude(),
                        'lon' => $garage->getCity()->getLongitude()
                    ],
                    'garageGoogleRating' => $garage->getGoogleRating()
                ];
                $indexableProUser->garages[] = $garageArray;
                if ($indexableProUser->maxGarageGoogleRating < $garageArray['garageGoogleRating']) {
                    $indexableProUser->maxGarageGoogleRating = $garageArray['garageGoogleRating'];
                }
            }
        }
        // Sort garages by Google Rating to help the function score in search query
        uasort($indexableProUser->garages, function ($garage1, $garage2) {
            if ($garage1['garageGoogleRating'] == $garage2['garageGoogleRating']) {
                return 0;
            }
            // Décroissant
            return ($garage1['garageGoogleRating'] < $garage2['garageGoogleRating']) ? 1 : -1;
        });
        return $indexableProUser;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return strval($this->id);
    }

    /**
     * @inheritDoc
     */
    public function shouldBeIndexed(): bool
    {
        return $this->deletedAt == null && !in_array('ROLE_ADMIN', $this->roles) && $this->isPublishable;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $arr = [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'presentationTitle' => $this->presentationTitle,
            'description' => $this->description,
            'descriptionLength' => $this->descriptionLength,
            'garages' => array_values($this->garages),
            'proServices' => array_values($this->proServices),
            'proSpecialities' => array_values($this->proSpecialities),
            'hasAvatar' => $this->hasAvatar
        ];
        if ($this->maxGarageGoogleRating > 0) {
            $arr['maxGaragesGoogleRating'] = $this->maxGarageGoogleRating;
        }
        return $arr;
    }
}