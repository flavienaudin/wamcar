<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
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
        $query = $this->createQueryBuilder('c')
            ->join('c.conversationUsers', 'cu', 'WITH', 'cu.user = :user')
            ->join('c.conversationUsers', 'cu2', 'WITH', 'cu2.user = :interlocutor')
            ->where('cu.conversation = cu2.conversation')
            ->setParameter('user', $user)
            ->setParameter('interlocutor', $interlocutor)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(BaseUser $user): array
    {
        $query = $this->createQueryBuilder('c')
            ->join('c.conversationUsers', 'cu', 'WITH', 'cu.user = :user')
            // Condition : interlocutor is not deleted
            ->join('c.conversationUsers', 'cu1', Expr\Join::WITH, 'cu1.user <> cu.user')
            ->join('cu1.user', 'u', Expr\Join::WITH, 'u.deletedAt is NULL AND u INSTANCE OF AppBundle\Doctrine\Entity\ProApplicationUser')
            // End Condition
            ->setParameter('user', $user)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery();

        return $query->getResult();
    }
}
