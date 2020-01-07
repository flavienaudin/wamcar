<?php


namespace AppBundle\Form\DTO;


use Wamcar\User\ProUser;

class ProUserPresentationDTO
{

    /** @var string */
    public $presentationTitle;

    /** @var string */
    public $description;

    /**
     * UserPresentationTypeDTO constructor.
     * @param ProUser $user
     */
    public function __construct(ProUser $user)
    {
        $this->presentationTitle = $user->getPresentationTitle();
        $this->description = $user->getUserProfile()->getDescription();
    }
}