<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationRepository;

class DoctrineConversationRepository extends EntityRepository implements ConversationRepository
{
    /**
     * {@inheritdoc}
     */
    public function update(Conversation $conversation): Conversation
    {
        $this->_em->persist($conversation);
        $this->_em->flush();

        return $conversation;
    }
}
