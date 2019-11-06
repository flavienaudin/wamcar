<?php

namespace AppBundle\Form\DTO;


use Wamcar\User\ProService;

class SearchProDTO
{
    /** @var string */
    public $text;
    /** @var ProService */
    public $speciality;
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