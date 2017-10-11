<?php


namespace AppBundle\Doctrine\Repository;


use AppBundle\DTO\Form\EditUserData;
use AppBundle\Entity\ApplicationUser;

interface InformationsUpdatable
{
    /**
     * @param EditUserData $userData
     * @return ApplicationUser
     */
    public function updateInformations(EditUserData $userData): ApplicationUser;

}
