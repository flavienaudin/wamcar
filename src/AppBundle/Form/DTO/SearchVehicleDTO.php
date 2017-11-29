<?php

namespace AppBundle\Form\DTO;


use Novaway\ElasticsearchClient\Filter\TermFilter;

class SearchVehicleDTO
{
    /** @var string */
    public $queryTerm;
    /** @var string */
    public $text;
    /** @var string */
    public $postalCode;
    /** @var string */
    public $cityName;
    /** @var string */
    public $latitude;
    /** @var string */
    public $longitude;
    /** @var string */
    public $sort;


    /**
     * SearchFilterData constructor.
     * @param string $queryTerm
     */
    public function __construct(string $queryTerm = null)
    {
        $this->queryTerm = $queryTerm;
    }
    /**
     * @return bool
     */
    public function hasQueryTerm(): bool
    {
        return null !== $this->queryTerm;
    }

    /**
     * @return bool
     */
    public function hasFilter(): bool
    {
        return null !== $this->text
            ;
    }

    /**
     * @return bool
     */
    public function isStorable(): bool
    {
        return $this->hasFilter() || $this->hasQueryTerm();
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

}
