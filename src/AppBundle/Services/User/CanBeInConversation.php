<?php


namespace AppBundle\Services\User;


use Doctrine\Common\Collections\Collection;

interface CanBeInConversation
{

    /**
     * @return Collection <Message>
     */
    public function getMessages(): Collection;

    /**
     * @return Collection <ConversationUser>
     */
    public function getConversationUsers(): Collection;

}
