<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
        $query = $this->createQueryBuilder('m')
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
        // TODO : exclude conversation with deleted interlocutor
        $query = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->join('m.conversation', 'c')
            ->join('c.conversationUsers', 'cu', 'WITH', 'm.publishedAt >= cu.lastOpenedAt AND m.user != :user')
            ->where('cu.user = :user')
            ->setParameter('user', $user)
            ->orderBy('m.publishedAt', 'ASC')
            ->getQuery();

        return $query->getSingleScalarResult();
    }


    /**
     * {@inheritdoc}
     */
    public function getCountSentMessages(BaseUser $user, ?int $sinceDays = 30, ?\DateTimeInterface $referenceDate = null): int
    {
        if (empty($referenceDate)) {
            $referenceDate = new \DateTime();
        }
        $firstDate = clone $referenceDate;
        $firstDate->sub(new \DateInterval('P' . $sinceDays . 'D'));
        $qb = $this->createQueryBuilder('m');
        $qb->select('COUNT(m.id)')
            ->where('m.user = :user')
            ->andWhere('m.publishedAt >= :firstDate')
            ->andWhere('m.publishedAt < :referenceDate')
            ->setParameter('user', $user)
            ->setParameter('firstDate', $firstDate)
            ->setParameter('referenceDate', $referenceDate);
        return $qb->getQuery()->getSingleScalarResult();
    }


    /**
     * {@inheritdoc}
     */
    public function getCountReceivedMessages(BaseUser $user, ?int $sinceDays = 30, ?\DateTimeInterface $referenceDate = null): int
    {
        if (empty($referenceDate)) {
            $referenceDate = new \DateTime();
        }
        $subQB = $this->getEntityManager()->createQueryBuilder();
        $subQB->select('DISTINCT IDENTITY(cu.conversation)')
            ->from("Wamcar:Conversation\ConversationUser", 'cu')
            ->where($subQB->expr()->eq('cu.user', ':user'));

        $firstDate = clone $referenceDate;
        $firstDate->sub(new \DateInterval('P' . $sinceDays . 'D'));
        $qb = $this->createQueryBuilder('m');
        $qb->select('COUNT(m.id)')
            ->where($qb->expr()->in('m.conversation', $subQB->getDQL()))
            ->andWhere($qb->expr()->neq('m.user', ':user'))
            ->andWhere($qb->expr()->gte('m.publishedAt', ':firstDate'))
            ->andWhere($qb->expr()->lte('m.publishedAt', ':referenceDate'))
            ->setParameter('user', $user)
            ->setParameter('firstDate', $firstDate)
            ->setParameter('referenceDate', $referenceDate);

        $query = $qb->getQuery();
        return $query->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastVehicleHeader(Conversation $conversation): ?Message
    {
        $query = $this->createQueryBuilder('m')
            ->where('m.conversation = :conversation')
            ->andWhere('m.personalVehicleHeader IS NOT NULL OR m.proVehicleHeader IS NOT NULL')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.publishedAt', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getQuery();

        //http://www.christophe-meneses.fr/article/setmaxresults-limite-le-resultat-de-mes-jointures
        $query = new Paginator($query);
        $messages = $query->getIterator();

        return end($messages) ?: null;
    }
}
