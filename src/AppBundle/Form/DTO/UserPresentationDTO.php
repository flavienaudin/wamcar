<?php


namespace AppBundle\Form\DTO;


use Wamcar\User\BaseUser;
use Wamcar\User\PersonalUser;

class UserPresentationDTO
{

    /** @var string */
    public $description;

    /**
     * UserPresentationDTO constructor.
     * @param PersonalUser $user
     */
    public function __construct(BaseUser $user)
    {
        $this->description = $user->getUserProfile()->getDescription();
    }
}