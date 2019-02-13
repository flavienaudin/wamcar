<?php

namespace AppBundle\Form\DTO;


use Wamcar\Vehicle\Enum\Sorting;

class SearchVehicleDTO
{
    /** @var array Search objet (Cf SearchTypeChoice) */
    public $type;
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
    /** @var int */
    public $radius = 50;
        /** @var string */
    public $make;
    /** @var string */
    public $model;
    /** @var string */
    public $mileageMax;
    /** @var string */
    public $yearsMin;
    /** @var string */
    public $yearsMax;
    /** @var string */
    public $budgetMin;
    /** @var string */
    public $budgetMax;
    /** @var string */
    public $transmission;
    /** @var string */
    public $fuel;
    /** @var string */
    public $sorting = Sorting::SEARCH_SORTING_DATE;
    /** @var string */
    public $tab;

}
