<?php

namespace AppBundle\Elasticsearch\Type;


use AppBundle\Doctrine\Entity\ProApplicationUser;
use Novaway\ElasticsearchClient\Indexable;
use Wamcar\Garage\GarageProUser;

class IndexableProUser implements Indexable
{
    const TYPE = 'pro_user';

    /** @var int $id */
    private $id;
    /** @var string $firstName */
    private $firstName;
    /** @var null|string $lastName */
    private $lastName;
    /** @var null|string $description */
    private $description;
    /** @var array */
    private $garages = [];
    /** @var float */
    private $maxGarageGoogleRating;
    /** @var (Role|string)[] */
    private $roles;

    /**
     * IndexableProUser constructor.
     * @param int $id
     * @param string $firstName
     * @param null|string $lastName
     * @param null|string $description
     * @param array|null $garages
     */
    private function __construct(int $id, string $firstName, ?string $lastName, ?string $description, array $garages = [], array $roles = [])
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->description = $description;
        $this->garages = $garages;
        $this->roles = $roles;
    }

    public static function createFromProApplicationUser(ProApplicationUser $proApplicationUser): IndexableProUser
    {
        $indexableProUser = new self(
            $proApplicationUser->getId(),
            $proApplicationUser->getFirstName(),
            $proApplicationUser->getLastName(),
            $proApplicationUser->getDescription(),
            [],
            $proApplicationUser->getRoles()
        );
        $indexableProUser->maxGarageGoogleRating = -1;
        /** @var GarageProUser $garageMembership */
        foreach ($proApplicationUser->getEnabledGarageMemberships() as $garageMembership) {
            $garage = $garageMembership->getGarage();
            $garageArray = [
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
        return !in_array('ROLE_ADMIN', $this->roles);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $arr = [
            'type' => self::TYPE,
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'description' => $this->description,
            'garages' => array_values($this->garages)
        ];
        if ($this->maxGarageGoogleRating > 0) {
            $arr['maxGaragesGoogleRating'] = $this->maxGarageGoogleRating;
        }
        return $arr;
    }
}