<?php

namespace Wamcar\Conversation;

use Wamcar\User\BaseUser;

interface ConversationRepository
{
    /**
     * @param Conversation $conversation
     * @return Conversation
     */
    public function update(Conversation $conversation): Conversation;

    /**
     * @param BaseUser $user
     * @param BaseUser $interlocutor
     * @return null|Conversation
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByUserAndInterlocutor(BaseUser $user, BaseUser $interlocutor): ?Conversation;

    /**
     * @param BaseUser $user
     * @return array
     */
    public function findByUser(BaseUser $user): array;
}
