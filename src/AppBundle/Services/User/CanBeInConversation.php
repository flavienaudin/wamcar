<?php


namespace AppBundle\Services\User;


use Doctrine\Common\Collections\ArrayCollection;

interface CanBeInConversation
{

    /**
     * @return ArrayCollection
     */
    public function getMessages(): ArrayCollection;

    /**
     * @return ArrayCollection
     */
    public function getConversations(): ArrayCollection;

}
