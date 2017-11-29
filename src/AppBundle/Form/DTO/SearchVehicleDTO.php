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

}
