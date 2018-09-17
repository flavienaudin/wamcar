<?php

namespace Wamcar\User;


use AppBundle\Doctrine\Entity\UserPreferences;

interface UserPreferencesRepository
{

    /**
     * @param UserPreferences $userPreferences
     *
     * @return UserPreferences
     */
    public function update(UserPreferences $userPreferences);

}