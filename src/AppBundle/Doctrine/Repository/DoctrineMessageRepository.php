<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\Message;
use Wamcar\Conversation\MessageRepository;
use Wamcar\User\BaseUser;

class DoctrineMessageRepository extends EntityRepository implements MessageRepository
{


    /**
     * {@inheritdoc}
     */
    public function getLastConversationMessage(Conversation $conversation): ?Message
    {
        $messages = $this->findByConversationAndOrdered($conversation);

        return end($messages) ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function findByConversationAndOrdered(Conversation $conversation): array
    {
        $query =  $this->createQueryBuilder('m')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.publishedAt', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getCountUnreadMessagesByUser(BaseUser $user): int
    {
        $query =  $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->join('m.conversation', 'c')
            ->join('c.conversationUsers', 'cu', 'WITH', 'm.publishedAt > cu.lastOpenedAt AND m.user = :user')
            ->where('cu.user = :user')
            ->setParameter('user', $user)
            ->orderBy('m.publishedAt', 'ASC')
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastVehicleHeader(Conversation $conversation, BaseUser $user): ?Message
    {
        $query =  $this->createQueryBuilder('m')
            ->where('m.user = :user')
            ->andWhere('m.conversation = :conversation')
            ->andWhere('m.personalVehicleHeader IS NOT NULL OR m.proVehicleHeader IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('conversation', $conversation)
            ->orderBy('m.publishedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
