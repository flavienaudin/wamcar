<?php


namespace AppBundle\Services\User;


use Doctrine\Common\Collections\Collection;
use Wamcar\Conversation\ConversationUser;
use Wamcar\Conversation\Message;

interface CanBeInConversation
{

    /**
     * @return Message[]|Collection
     */
    public function getMessages(): Collection;

    /**
     * @return ConversationUser[]|Collection
     */
    public function getConversationUsers(): Collection;

}
