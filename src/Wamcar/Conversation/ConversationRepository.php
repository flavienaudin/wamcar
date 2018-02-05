<?php

namespace Wamcar\Conversation;

interface ConversationRepository
{
    /**
     * @param Conversation $conversation
     *
     * @return Conversation
     */
    public function update(Conversation $conversation): Conversation;
}
