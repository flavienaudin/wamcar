<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\Message;
use Wamcar\Conversation\MessageRepository;

class DoctrineMessageRepository extends EntityRepository implements MessageRepository
{


    /**
     * {@inheritdoc}
     */
    public function getLastConversationMessage(Conversation $conversation): ?Message
    {
        $messages = $this->findByConversationAndOrdered($conversation);

        return count($messages) > 0 ? $messages[count($messages) - 1] : null;
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
}
