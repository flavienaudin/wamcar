<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationUser;
use Wamcar\Conversation\ConversationUserRepository;
use Wamcar\User\BaseUser;

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

    /**
     * {@inheritdoc}
     */
    public function findByConversationAndUser(Conversation $conversation, BaseUser $user): ?ConversationUser
    {
        /** @var ConversationUser $conversationUser */
        foreach ($conversation->getConversationUsers() as $conversationUser) {
            if ($conversationUser->getUser()->getId() === $user->getId()) {
                return $conversationUser;
            }
        }

        return null;
    }
}
