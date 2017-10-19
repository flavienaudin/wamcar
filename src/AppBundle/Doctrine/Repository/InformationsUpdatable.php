<?php


namespace AppBundle\Doctrine\Repository;


use AppBundle\Entity\ApplicationUser;
use AppBundle\Form\DTO\EditUserData;

interface InformationsUpdatable
{
    /**
     * @param EditUserData $userData
     * @return ApplicationUser
     */
    public function updateInformations(EditUserData $userData): ApplicationUser;

}
