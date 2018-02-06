<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\Conversation\ConversationUser;
use Wamcar\Conversation\ConversationUserRepository;

class DoctrineConversationUserRepository extends EntityRepository implements ConversationUserRepository
{
    /**
     * {@inheritdoc}
     */
    public function update(ConversationUser $conversationUser): ConversationUser
    {
        $this->_em->persist($conversationUser);
        $this->_em->flush();

        return $conversationUser;
    }
}
