<?php


namespace AppBundle\Doctrine\Repository;


use AppBundle\Entity\ApplicationUser;
use AppBundle\Form\DTO\EditUserData;
use AppBundle\Form\DTO\UserInformationDTO;

interface InformationsUpdatable
{
    /**
     * @param UserInformationDTO $userInformationDTO
     * @return ApplicationUser
     */
    public function updateInformations(UserInformationDTO $userInformationDTO): ApplicationUser;

}
