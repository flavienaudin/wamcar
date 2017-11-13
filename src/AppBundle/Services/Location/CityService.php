<?php

namespace AppBundle\Services\Location;

use PragmaRX\ZipCode\ZipCode;


class CityService
{
    /** @var ZipCode */
    private $zipcode;

    /**
     * CityService constructor.
     * @param ZipCode $zipcode
     */
    public function __construct(
        ZipCode $zipcode
    )
    {
        $this->zipcode = $zipcode;
        $this->zipcode->setCountry('FR');
    }

    public function findByZipcode($zipcode)
    {
        $city = $this->zipcode->find($zipcode);

        return $city;
    }

}
