<?php

namespace AppBundle\Doctrine\Entity;

<<<<<<< fb2175bd738e4945cc3fb3a8d0ba377d679b58d9
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
=======

use AppBundle\Services\Garage\HasMember;
use Wamcar\Garage\Address;
>>>>>>> add relation between prouser and garage
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;

class ApplicationGarage extends Garage
{
    use SoftDeleteable;

    /** @var string */
    protected $deletedAt;

}
