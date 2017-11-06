<?php

namespace AppBundle\Doctrine\Entity;

use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Wamcar\Garage\Garage;

class ApplicationGarage extends Garage
{
    use SoftDeleteable;

    /** @var string */
    protected $deletedAt;

}
