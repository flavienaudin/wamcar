<?php


namespace AppBundle\Services\User;


use Doctrine\Common\Collections\Collection;
use Wamcar\Conversation\ConversationUser;

interface CanBeInConversation
{

    /**
     * @return Collection
     */
    public function getMessages(): Collection;

    /**
     * @return ConversationUser[]|Collection
     */
    public function getConversationUsers(): Collection;

}
