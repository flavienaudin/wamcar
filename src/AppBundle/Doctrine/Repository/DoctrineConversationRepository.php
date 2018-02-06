<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationRepository;
use Wamcar\User\BaseUser;

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

    /**
     * {@inheritdoc}
     */
    public function findByUserAndInterlocutor(BaseUser $user, BaseUser $interlocutor): ?Conversation
    {
        $query =  $this->createQueryBuilder('c')
            ->join('c.conversationUsers', 'cu')
            ->join('c.conversationUsers', 'cu2')
            ->where('cu.user = :user')
            ->andWhere('cu2.user = :interlocutor')
            ->andWhere('cu.conversation = cu2.conversation')
            ->setParameter('user', $user)
            ->setParameter('interlocutor', $interlocutor)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
