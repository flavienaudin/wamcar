<?php

namespace Wamcar\Conversation;


use Wamcar\User\BaseUser;

interface ConversationUserRepository
{
    /**
     * @param ConversationUser $conversationUser
     * @return ConversationUser
     */
    public function update(ConversationUser $conversationUser): ConversationUser;

    /**
     * @param Conversation $conversation
     * @param BaseUser $user
     * @return null|ConversationUser
     */
    public function findByConversationAndUser(Conversation $conversation, BaseUser $user): ?ConversationUser;
}
