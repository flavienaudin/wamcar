<?php

namespace Wamcar\Conversation;


interface ConversationUserRepository
{
    /**
     * @param ConversationUser $conversationUser
     * @return ConversationUser
     */
    public function update(ConversationUser $conversationUser): ConversationUser;

}
