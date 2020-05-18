<?php


namespace AppBundle\Form\DTO;


use Wamcar\Garage\Garage;

class GaragePresentationDTO
{

    /** @var string */
    public $presentation;

    /**
     * GaragePresentationDTO constructor.
     * @param Garage $garage
     */
    public function __construct(Garage $garage)
    {
        $this->presentation = $garage->getPresentation();
    }

}