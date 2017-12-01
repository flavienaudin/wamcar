<?php

namespace AppBundle\Form\DTO;


use Novaway\ElasticsearchClient\Filter\TermFilter;

class SearchVehicleDTO
{
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

}
