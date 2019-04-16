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
     * @param BaseUser $user L'utilisateur ayant envoyé les messages
     * @param int|null $sinceDays Intervalle de temps avant la date de référence
     * @param \DateTimeInterface|null $referenceDate Date de référence (par défaut le jour même)
     * @return int
     */
    public function getCountSentMessages(BaseUser $user, ?int $sinceDays = 30, ?\DateTimeInterface $referenceDate = null): int;

    /**
     * @param BaseUser $user L'utilisateur ayant reçu les messages
     * @param int|null $sinceDays Intervalle de temps avant la date de référence
     * @param \DateTimeInterface|null $referenceDate Date de référence (par défaut le jour même)
     * @return int
     */
    public function getCountReceivedMessages(BaseUser $user, ?int $sinceDays = 30, ?\DateTimeInterface $referenceDate = null): int;

    /**
     * @param Conversation $conversation
     * @return null|Message
     */
    public function getLastVehicleHeader(Conversation $conversation): ?Message;
}
