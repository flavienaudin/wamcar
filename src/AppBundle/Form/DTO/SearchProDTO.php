<?php

namespace AppBundle\Form\DTO;


class SearchProDTO
{
    /** @var array */
    public $filters;
    /** @var string */
    public $text;
    /** @var bool */
    public $searchTextInService = false;
    /** @var string */
    public $postalCode;
    /** @var string */
    public $cityName;
    /** @var string */
    public $latitude;
    /** @var string */
    public $longitude;
    /** @var string */
    public $radius;
    /** @var string */
    public $sorting;
}