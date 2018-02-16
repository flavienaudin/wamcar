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
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountUnreadMessagesByUser(BaseUser $user): int;

    /**
     * @param Conversation $conversation
     * @param BaseUser $user
     * @return null|Message
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastVehicleHeader(Conversation $conversation, BaseUser $user): ?Message;

}
