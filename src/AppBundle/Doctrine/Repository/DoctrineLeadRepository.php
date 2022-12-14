<?php

namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Wamcar\User\BaseUser;
use Wamcar\User\Lead;
use Wamcar\User\LeadRepository;
use Wamcar\User\ProUser;

class DoctrineLeadRepository extends EntityRepository implements LeadRepository
{
    /**
     * {@inheritdoc}
     */
    public function getPotentialLeadsByProUser(ProUser $proUser): array
    {
        // TODO : recupérér différement les leads potentiels ? Connaitre qui a initié le lead
        $con = $this->_em->getConnection();
        $res = $con->executeQuery(
            "select u1.userId as leadUserId, sum(nb_messages) as nbMessages, sum(nb_like) as nbLikes, min(contactedAt) as createdAt, max(contactedAt) as contactedAt
                    from (
                        (select nbMess.userId as userId, nb_messages, 0 as nb_like, nbMess.contactedAt as contactedAt
                        from (
                            select cu.user_id as userId, count(cm.id) as nb_messages, max(cm.published_at) as contactedAt
                            from conversation_user cu join conversation_message cm on cm.conversation_id = cu.conversation_id
                            where cu.conversation_id in (select conversation_id from conversation_user where user_id = :proUserId)
                                and cu.user_id != :proUserId
                            group by cu.user_id
                            ) nbMess
                        )union(
                        select nbLikes.userId as userId, 0 as nb_messages, nbLikes.nb_like, nbLikes.contactedAt as contactedAt
                        from (
                            select user_id as userId, count(ul.id) as nb_like, max(ul.updated_at) as contactedAt
                            from user_like_vehicle ul where ul.vehicle_id in (select id from pro_vehicle where seller_id = :proUserId) 
                                and ul.user_id != :proUserId
                            group by userId
                            ) nbLikes
                        )
                    ) u1
                    group by u1.userId;",
            ['proUserId' => $proUser->getId()]
        );
        return $res->fetchAll();
    }

    /**
     * @param ProUser $proUser
     * @param array $params : https://datatables.net/manual/server-side#Sent-parameters
     * @return array : https://datatables.net/manual/server-side#Returned-data
     */
    public function getLeadsByRequest(ProUser $proUser, array $params): array
    {
        // Query count total filtered results
        $qb = $this->createQueryBuilder('l');
        $qb->select($qb->expr()->count('l'))
            ->where($qb->expr()->eq('l.proUser', ':proUser'));
        $qb->setParameter('proUser', $proUser);
        if (isset($params['search']) && !empty($params['search']['value'])) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('l.firstName', ':searchValue'),
                    $qb->expr()->like('l.lastName', ':searchValue')
                )
            );
            $qb->setParameter('searchValue', '%' . $params['search']['value'] . '%');
        }
        try {
            $count = $qb->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            $count = null;
        }


        // Query filtered results
        $qb = $this->createQueryBuilder('l');
        $qb->where($qb->expr()->eq('l.proUser', ':proUser'))
            ->setFirstResult($params['start'])
            ->setMaxResults($params['length']);
        $qb->setParameter('proUser', $proUser);

        if (isset($params['search']) && !empty($params['search']['value'])) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('l.firstName', ':searchValue'),
                    $qb->expr()->like('l.lastName', ':searchValue')
                )
            );
            $qb->setParameter('searchValue', '%' . $params['search']['value'] . '%');
        }

        foreach ($params['order'] as $order) {
            if ($order['column'] > 0) {
                $orderColumns = [
                    1 => 'l.lastContactedAt',
                    2 => 'l.nbPhoneAction',
                    3 => 'l.nbPhoneProAction',
                    4 => 'l.nbMessages',
                    5 => 'l.nbLikes',
                    6 => 'l.status'
                ];
                $qb->addOrderBy($orderColumns[$order['column']], $order['dir']);
            } else {
                $qb->addOrderBy('l.firstName', $order['dir']);
                $qb->addOrderBy('l.lastName', $order['dir']);
            }
        }


        return ['data' => $qb->getQuery()->getResult(), 'recordsFilteredCount' => $count];
    }

    /**
     * {@inheritdoc}
     */
    public function getCountLeadsByLastDateOfContact(BaseUser $user, ?int $sinceDays = 30, ?\DateTimeInterface $referenceDate = null): int
    {
        if (empty($referenceDate)) {
            $referenceDate = new \DateTime();
        }
        $firstDate = clone $referenceDate;
        $firstDate->sub(new \DateInterval('P' . $sinceDays . 'D'));

        $qb = $this->createQueryBuilder('l');
        $qb->select('COUNT(l.id)')
            ->where($qb->expr()->eq('l.proUser', ':user'))
            ->andwhere($qb->expr()->gte('l.lastContactedAt', ':firstDate'))
            ->andWhere($qb->expr()->lte('l.lastContactedAt', ':referenceDate'))
            ->setParameter('user', $user)
            ->setParameter('firstDate', $firstDate)
            ->setParameter('referenceDate', $referenceDate);
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get statistics of the $proUser's actions on its leads
     * @param ProUser $proUser
     * @return array
     */
    public function getProUserActionsStats(ProUser $proUser): array
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select('COUNT(l.id) as nbPhoneDisplays, MAX(l.lastContactedAt) as lastActionDate')
            ->where($qb->expr()->eq('l.proUser', ':prouser'))
            ->andWhere($qb->expr()->gt('l.nbPhoneActionByPro + l.nbPhoneProActionByPro', 0))
            ->setParameter('prouser', $proUser);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Get statistics about the $user as Lead of ProUser
     * @param BaseUser $user
     * @return array
     */
    public function getLeadUserActionsStats(BaseUser $user): array
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select('COUNT(l.id) as nbPhoneDisplays, MAX(l.lastContactedAt) as lastActionDate')
            ->where($qb->expr()->eq('l.userLead', ':user'))
            ->andWhere($qb->expr()->gt('l.nbPhoneActionByLead + l.nbPhoneProActionByLead', 0))
            ->setParameter('user', $user);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Reset counters of Messages/Likes of all Leads
     */
    public function resetCountersMessageAndLikes()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->update('Wamcar:User\Lead', 'l')
            ->set('l.nbLeadMessages', 0)
            ->set('l.nbLeadLikes', 0)
            ->set('l.nbProMessages', 0)
            ->set('l.nbProLikes', 0);
        return $qb->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function add(Lead $lead): Lead
    {
        $this->_em->persist($lead);
        $this->_em->flush();
        return $lead;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Lead $lead): Lead
    {
        $this->_em->persist($lead);
        $this->_em->flush();
        return $lead;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Lead $lead)
    {
        $this->_em->remove($lead);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function saveBulk(array $leads, ?int $batchSize = 50)
    {
        $idx = 0;
        /** @var Lead $lead */
        foreach ($leads as $lead) {
            $idx++;
            $this->_em->persist($lead);
            if (($idx % $batchSize) === 0) {
                $this->_em->flush();
            }
        }
        $this->_em->flush();
    }
}