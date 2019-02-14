<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationUser;
use Wamcar\Conversation\ConversationUserRepository;
use Wamcar\Garage\GarageProUser;
use Wamcar\User\BaseUser;
use Wamcar\User\PersonalUser;

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
        $query = $this->createQueryBuilder('cu')
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
        $query = $this->createQueryBuilder('cu')
            ->where('cu.conversation = :conversation')
            ->andWhere('cu.user != :user')
            ->setParameter('conversation', $conversation)
            ->setParameter('user', $user)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Get ConversationUsers of User that are in conversation with the given personnal user and which user are member of the given garages
     * @param PersonalUser $personalUser
     * @param array $garages
     * @return BaseUser[]
     */
    public function findContactsOfGarages(PersonalUser $personalUser, array $garages): array
    {
        $expr = $this->_em->getExpressionBuilder();
        $qb = $this->createQueryBuilder('cu')
            ->where(
                $expr->in('cu.conversation',
                    $this->_em->createQueryBuilder()
                        ->select('c.id')
                        ->from(Conversation::class, 'c')
                        ->join('c.conversationUsers', 'scu')
                        ->where($expr->eq('scu.user', ':personalUser'))
                        ->setParameter(':personalUser', $personalUser)
                        ->getDQL()
                )
            )
            ->andWhere(
                $expr->neq('cu.user', ':personalUser')
            )
            ->andWhere(
                $expr->in('cu.user',
                    $this->_em->createQueryBuilder()
                        ->select('IDENTITY(gpu.proUser)')
                        ->from(GarageProUser::class, 'gpu')
                        ->join('gpu.proUser', 'pu')
                        ->where($expr->in('gpu.garage', ':garages'))
                        ->setParameter(':garages', $garages)
                        ->getDQL()
                )
            )
            ->setParameter(':personalUser', $personalUser)
            ->setParameter(':garages', $garages);
        return $qb->getQuery()->getResult();
    }
}
