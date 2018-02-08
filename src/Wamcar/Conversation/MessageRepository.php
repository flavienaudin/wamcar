<?php

namespace Wamcar\Conversation;



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

}
