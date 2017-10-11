<?php


namespace Wamcar\User;


class City
{
    /** @var string  */
    private $postalCode;
    /** @var string  */
    private $city;

    public function __construct(
        string $postalCode = null,
        string $city = null
    )
    {
        $this->postalCode = $postalCode;
        $this->city = $city;
    }

}
