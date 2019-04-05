<?php

namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
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
        $con = $this->_em->getConnection();
        $res = $con->executeQuery(
            "select u1.userId as leadUserId, sum(nb_messages) as nbMessages, sum(nb_like) as nbLikes, max(contactedAt) as contactedAt
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
                    group by u1.userId
                    ;",
            ['proUserId' => $proUser->getId()]
        );
        return $res->fetchAll();
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
}