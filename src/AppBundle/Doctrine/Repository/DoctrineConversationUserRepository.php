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
        $query =  $this->createQueryBuilder('cu')
            ->where('cu.conversation = :conversation')
            ->andWhere('cu.user = :user')
            ->setParameter('conversation', $conversation)
            ->setParameter('user', $user)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findInterlocutorConversation(Conversation $conversation, BaseUser $user): ?ConversationUser
    {
        $query =  $this->createQueryBuilder('cu')
            ->where('cu.conversation = :conversation')
            ->andWhere('cu.user != :user')
            ->setParameter('conversation', $conversation)
            ->setParameter('user', $user)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
