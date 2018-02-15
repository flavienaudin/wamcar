<?php

namespace Wamcar\Conversation;



use Wamcar\User\BaseUser;

interface MessageRepository
{
    /**
     * @param Conversation $conversation
     * @return null|Message
     */
    public function getLastConversationMessage(Conversation $conversation): ?Message;

    /**
     * @param Conversation $conversation
     * @return array
     */
    public function findByConversationAndOrdered(Conversation $conversation): array;

    /**
     * @param BaseUser $user
     * @return array
     */
    public function findUnreadMessagesByUser(BaseUser $user): array;

}
