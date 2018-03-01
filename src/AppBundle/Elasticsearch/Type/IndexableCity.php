<?php

namespace AppBundle\Elasticsearch\Type;

use Novaway\ElasticsearchClient\Indexable;

class IndexableCity implements Indexable
{
    const TYPE = 'city';

    /** @var string */
    private $insee;
    /** @var string */
    private $postalCode;
    /** @var string */
    private $cityName;
    /** @var string */
    private $latitude;
    /** @var string */
    private $longitude;

    /**
     * City constructor.
     * @param string $insee
     * @param string $postalCode
     * @param string $cityName
     * @param string $latitude
     * @param string $longitude
     */
    public function __construct(string $insee,
                                string $postalCode,
                                string $cityName,
                                string $latitude,
                                string $longitude
    )
    {
        $this->insee = $insee;
        $this->postalCode = $postalCode;
        $this->cityName = $cityName;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->insee;
    }

    /**
     * @return bool
     */
    public function shouldBeIndexed(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'insee' => $this->insee,
            'postalCode' => $this->postalCode,
            'cityName' => $this->cityName,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];
    }

}
