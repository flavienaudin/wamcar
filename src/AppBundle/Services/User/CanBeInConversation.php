<?php


namespace AppBundle\Services\User;


use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;

interface CanBeInConversation
{

    /**
     * @return array
     */
    public function getUserConversations(): array;

}
