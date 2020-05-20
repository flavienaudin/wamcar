<?php


namespace AppBundle\Form\DTO;


use Wamcar\User\ProUser;

class ProUserPresentationDTO extends UserPresentationDTO
{

    /** @var string */
    public $presentationTitle;

    /**
     * ProUserPresentationDTO constructor.
     * @param ProUser $user
     */
    public function __construct(ProUser $user)
    {
        parent::__construct($user);
        $this->presentationTitle = $user->getPresentationTitle();
    }
}