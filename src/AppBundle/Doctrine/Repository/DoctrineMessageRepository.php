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
        $query =  $this->createQueryBuilder('m')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.publishedAt', 'DESC')
            ->getQuery();

        return count($query->getResult()) > 0 ? $query->getResult()[0] : null;
    }
}
