<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\UserInterface;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\LeadNewRegistrationEvent;
use Wamcar\User\Event\UserLikeVehicleEvent;
use Wamcar\Vehicle\Enum\NotificationFrequency;

trait DoctrineUserRepositoryTrait
{

    /**
     * Get users who have unread notifications or messages during the last 24h, in order to send them an email according to their preferences
     *
     * @return array
     * @throws \Exception when the interval_spec cannot be parsed as an interval.
     */
    public function getUsersWithWaitingNotificationsOrMessages(int $sinceLastHours = 24)
    {
        /** @var QueryBuilder $mainQB */
        $mainQB = $this->createQueryBuilder('u');
        $mainQB->join('u.preferences', 'up');

        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        // Unread notifications
        $unreadNotifQB = $em->createQueryBuilder();
        $unreadNotifQB->select('1')
            ->from('AppBundle:Doctrine\Entity\EventNotification', 'n')
            ->join('n.notifiableNotifications', 'nn')
            ->join('nn.notifiableEntity', 'ne')
            ->where($unreadNotifQB->expr()->eq('ne.identifier', 'u.id'))
            ->andwhere($unreadNotifQB->expr()->eq('nn.seen', $unreadNotifQB->expr()->literal(false)))
            ->andWhere($unreadNotifQB->expr()->between('n.date', ':select_interval_start', ':select_interval_end'))
            ->andWhere($unreadNotifQB->expr()->orX(
                $unreadNotifQB->expr()->andX(
                    $unreadNotifQB->expr()->eq('n.event', $unreadNotifQB->expr()->literal(UserLikeVehicleEvent::class)),
                    $unreadNotifQB->expr()->eq('up.likeEmailEnabled', $unreadNotifQB->expr()->literal(true))
                ),
                $unreadNotifQB->expr()->andX(
                    $unreadNotifQB->expr()->eq('n.event', $unreadNotifQB->expr()->literal(LeadNewRegistrationEvent::class)),
                    $unreadNotifQB->expr()->eq('up.leadEmailEnabled', $unreadNotifQB->expr()->literal(true))
                )
            ));

        // Unread messages
        $messageQB = $em->createQueryBuilder();
        $messageQB->select('1')
            ->from('Wamcar:Conversation\Message', 'cm')
            ->join('cm.user', 'interlocutor', Join::WITH, $messageQB->expr()->isNull('interlocutor.deletedAt'))
            ->where($messageQB->expr()->eq('cm.conversation', 'cu.conversation'))
            ->andWhere($messageQB->expr()->neq('cm.user', 'cu.user'))
            ->andWhere($messageQB->expr()->gte('cm.publishedAt', 'cu.lastOpenedAt'))
            ->andWhere($messageQB->expr()->between('cm.publishedAt', ':select_interval_start', ':select_interval_end'));

        $conversationUserQB = $em->createQueryBuilder();
        $conversationUserQB->select('1')
            ->from('Wamcar:Conversation\ConversationUser', 'cu')
            ->where($conversationUserQB->expr()->eq('cu.user', 'u'))
            ->andWhere($conversationUserQB->expr()->eq('up.privateMessageEmailEnabled', $conversationUserQB->expr()->literal(true)))
            ->andWhere($conversationUserQB->expr()->exists($messageQB->getDQL()));

        $mainQB
            ->where($mainQB->expr()->eq('up.globalEmailFrequency', $mainQB->expr()->literal(NotificationFrequency::ONCE_A_DAY)))
            ->andWhere(
                $mainQB->expr()->orX(
                    $mainQB->expr()->exists($unreadNotifQB->getDQL()),
                    $mainQB->expr()->exists($conversationUserQB->getDQL())
                )
            );

        $selectIntervalStart = new \DateTime("now");
        $selectIntervalStart->sub(new \DateInterval('PT' . $sinceLastHours . 'H'));
        $mainQB->setParameter("select_interval_start", $selectIntervalStart);

        $selectIntervalEnd = new \DateTime("now");
        $mainQB->setParameter("select_interval_end", $selectIntervalEnd);
        return $mainQB->getQuery()->getResult();
    }

    /**
     * @param string $username
     * @return null|ApplicationUser
     */
    public function loadUserByUsername($username): ?ApplicationUser
    {
        return $this->findOneBy(['email' => $username]);
    }

    /**
     * @param UserInterface $user
     * @return null|ApplicationUser
     */
    public function refreshUser(UserInterface $user): ?ApplicationUser
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return ApplicationUser::class === $class || ProApplicationUser::class;
    }
    /** Fin des méthodes du UserProviderInterface */

    /**
     * {@inheritdoc}
     */
    public function findOne(int $userId): ?BaseUser
    {
        return $this->findOneBy(['id' => $userId]);
    }

    /**
     * @param string $email
     * @return BaseUser
     */
    public function findOneByEmail(string $email)
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @param $ids array Array of entities'id
     * @return array
     */
    public function findByIds(array $ids): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from($this->getClassName(), 'u')
            ->where($qb->expr()->in('u.id', $ids))
            ->orderBy($qb->expr()->asc('FIELD(u.id, :orderedIds ) '));
        $qb->setParameter('orderedIds', $ids);
        return $qb->getQuery()->getResult();
    }


    /**
     * {@inheritdoc}
     */
    public function add(BaseUser $user)
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(BaseUser $user)
    {
        $user = $this->_em->merge($user);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(BaseUser $user)
    {
        $this->_em->remove($user);
        $this->_em->flush();
    }
}
