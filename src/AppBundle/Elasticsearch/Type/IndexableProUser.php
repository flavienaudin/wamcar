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

    /**
     * IndexableProUser constructor.
     * @param int $id
     * @param string $firstName
     * @param null|string $lastName
     * @param null|string $description
     * @param array|null $garages
     */
    private function __construct(int $id, string $firstName, ?string $lastName, ?string $description, array $garages = [])
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->description = $description;
        $this->garages = $garages;
    }

    public static function createFromProApplicationUser(ProApplicationUser $proApplicationUser): IndexableProUser
    {
        $indexableProUser = new self(
            $proApplicationUser->getId(),
            $proApplicationUser->getFirstName(),
            $proApplicationUser->getLastName(),
            $proApplicationUser->getDescription(),
            []
        );
        /** @var GarageProUser $garageMembership */
        foreach ($proApplicationUser->getGarageMemberships() as $garageMembership) {
            $garage = $garageMembership->getGarage();
            $garageArray = [
                'garageName' => $garage->getName(),
                'garagePresentation' => $garage->getPresentation(),
                'garageCityName' => $garage->getCity()->getName(),
                'garageCityPostalCode' => $garage->getCity()->getPostalCode(),
                'garageCityLatitude' => $garage->getCity()->getLatitude(),
                'garageCityLongitude' => $garage->getCity()->getLongitude(),
                'garageGoogleRating' => $garage->getGoogleRating()
            ];
            $indexableProUser->garages[] = $garageArray;
        }

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
        return true;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'type' => self::TYPE,
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'description' => $this->description,
            'garages' => $this->garages
        ];
    }
}