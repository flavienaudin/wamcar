<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
        $query =  $this->createQueryBuilder('c')
            ->join('c.conversationUsers', 'cu', 'WITH', 'cu.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery();

        return $query->getResult();
    }
}
