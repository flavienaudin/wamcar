<?php

namespace AppBundle\Services\Notification;


use AppBundle\Doctrine\Entity\EventNotification;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Illuminate\Support\Collection;
use Mgilet\NotificationBundle\Entity\NotifiableEntity;
use Mgilet\NotificationBundle\Entity\NotifiableNotification;
use Mgilet\NotificationBundle\Entity\Notification;
use Mgilet\NotificationBundle\Entity\Repository\NotifiableNotificationRepository;
use Mgilet\NotificationBundle\Entity\Repository\NotifiableRepository;
use Mgilet\NotificationBundle\Entity\Repository\NotificationRepository;
use Mgilet\NotificationBundle\Event\NotificationEvent;
use Mgilet\NotificationBundle\Manager\NotificationManager;
use Mgilet\NotificationBundle\MgiletNotificationEvents;
use Mgilet\NotificationBundle\NotifiableInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Wamcar\Garage\Event\PendingRequestToJoinGarageCreatedEvent;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\UserLikeVehicleEvent;
use Wamcar\Vehicle\Enum\NotificationFrequency;

class NotificationManagerExtended
{
    const ORDER_UNSEEN_FIRST = "ORDER_UNSEEN_FIRST";
    const ORDER_DATE_DESC = "ORDER_DATE_DESC";
    const ORDER_DATE_ASC = "ORDER_DATE_ASC ";

    /** @var EventDispatcherInterface $eventDispatcher */
    private $dispatcher;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var NotificationManager */
    private $notificationManager;
    /** @var NotifiableRepository $notifiableEntityRepository */
    private $notifiableEntityRepository;
    /** @var NotifiableNotificationRepository $notifiableNotificationRepository */
    private $notifiableNotificationRepository;
    /** @var NotificationRepository $notificationRepository */
    private $notificationRepository;

    /**
     * NotificationManagerExtended constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityManagerInterface $entityManager
     * @param NotificationManager $notificationManager
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager, NotificationManager $notificationManager)
    {
        $this->dispatcher = $eventDispatcher;
        $this->notificationManager = $notificationManager;
        $this->entityManager = $entityManager;
        $this->notifiableEntityRepository = $entityManager->getRepository(NotifiableEntity::class);
        $this->notifiableNotificationRepository = $entityManager->getRepository(NotifiableNotification::class);
        $this->notificationRepository = $entityManager->getRepository(Notification::class);
    }

    /**
     * @return NotificationManager
     */
    public function getNotificationManager(): NotificationManager
    {
        return $this->notificationManager;
    }

    /**
     * @param string $subject
     * @param string $event
     * @param string|null $message
     * @param string|null $link
     *
     * @return EventNotification
     */
    public function createNotification($subject, $event, $message = null, $link = null)
    {
        $notification = new EventNotification($event);
        $notification
            ->setSubject($subject)
            ->setMessage($message)
            ->setLink($link);

        $event = new NotificationEvent($notification);
        $this->dispatcher->dispatch(MgiletNotificationEvents::CREATED, $event);

        return $notification;
    }

    /**
     * @param NotifiableInterface $notifiable
     * @param bool $seen
     * @param string $order
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getNotifications(NotifiableInterface $notifiable, $seen = null, $order = self::ORDER_DATE_DESC, $offset = null, $limit = null)
    {
        $qb = $this->notifiableNotificationRepository->findAllByNotifiableQb(
            $this->notificationManager->generateIdentifier($notifiable),
            ClassUtils::getRealClass(get_class($notifiable)),
            null
        );

        if ($seen !== null) {
            $whereSeen = $seen ? 1 : 0;
            $qb
                ->andWhere('nn.seen = :seen')
                ->setParameter('seen', $whereSeen);

        }
        switch ($order) {
            case self::ORDER_UNSEEN_FIRST:
                $qb->orderBy('nn.seen', Criteria::ASC);
                $qb->addOrderBy('n.date', Criteria::DESC);
                break;
            case self::ORDER_DATE_ASC:
                $qb->orderBy('n.date', Criteria::ASC);
                break;
            case self::ORDER_DATE_DESC:
            default:
                $qb->orderBy('n.date', Criteria::DESC);
        }

        if ($offset != null && $offset >= 0) {
            $qb->setFirstResult($offset);
        }

        if ($limit != null) {
            $qb->setMaxResults($limit);
        }

        //http://www.christophe-meneses.fr/article/setmaxresults-limite-le-resultat-de-mes-jointures
        $pagination = new Paginator($qb->getQuery());
        $notifications = $pagination->getIterator();

        $displayableNotifications = [];
        /** @var NotifiableNotification $notification */
        foreach ($notifications as $notification) {
            $displayableNotification = [];
            $displayableNotification['notifiableNotification'] = $notification;

            /** @var EventNotification $eventNotification */
            $eventNotification = $notification->getNotification();
            $displayableNotification['notificationEvent'] = $eventNotification->getEvent();

            $subjectRepo = $this->entityManager->getRepository($eventNotification->getSubject());
            $displayableNotification['subject'] = $subjectRepo->find(json_decode($eventNotification->getMessage(), true));

            if ($displayableNotification['subject'] != null) {
                switch ($eventNotification->getEvent()) {
                    // secuity checks
                    case UserLikeVehicleEvent::class:
                        if ($displayableNotification['subject']->getValue() < 1) {
                            // security if notification was not removed when unlike
                            $displayableNotification = null;
                        }
                        break;
                    case PendingRequestToJoinGarageCreatedEvent::class:
                        if ($displayableNotification['subject']->getRequestedAt() == null) {
                            // security if notification was not removed when accepted/cancelled/declined
                            $displayableNotification = null;
                        }
                        break;
                    default:
                }

                if ($displayableNotification != null) {
                    $displayableNotifications[] = $displayableNotification;
                }
            }
        }

        return $displayableNotifications;
    }

    /**
     * Note : possibilité à terme de supprimer cette méthode pour getNotificationByObjectDescriptionAndNotifiable()
     * @param array $description Can contain :
     *      - 'subject' Type of the notification's object : className
     *      - 'message' Identifier(s) of the notification's object : as json string
     *      - 'event'   Event which generated the notificatoin : className
     * @return EventNotification[]
     */
    public function getNotificationByObjectDescription(array $description): array
    {
        return $this->notificationRepository->findBy($description);
    }

    /**
     * @param array $description Can contain :
     *      - 'subject' Type of the notification's object : className
     *      - 'message' Identifier(s) of the notification's object : as json string
     *      - 'event'   Event which generated the notificatoin : className
     * @param null|NotifiableEntity $notifiableEntity
     * @return EventNotification[]
     */
    public function getNotificationByObjectDescriptionAndNotifiable(array $description, ?NotifiableEntity $notifiableEntity = null): array
    {
        $qb = $this->notificationRepository->createQueryBuilder('n');

        if ($notifiableEntity != null) {
            $qb->join('n.notifiableNotifications', 'nn');
            $qb->andWhere($qb->expr()->eq('nn.notifiableEntity', ':notifiableEntity'));
            $qb->setParameter('notifiableEntity', $notifiableEntity);
        }

        if (isset($description['subject']) && !empty($description['subject'])) {
            $qb->andWhere($qb->expr()->eq('n.subject', ':subject'));
            $qb->setParameter('subject', $description['subject']);
        }
        if (isset($description['message']) && !empty($description['message'])) {
            $qb->andWhere($qb->expr()->eq('n.message', ':message'));
            $qb->setParameter('message', $description['message']);
        }
        if (isset($description['event']) && !empty($description['event'])) {
            $qb->andWhere($qb->expr()->eq('n.event', ':event'));
            $qb->setParameter('event', $description['event']);
        }
        return $qb->getQuery()->execute();
    }

    /**
     * Get last 24h notifications that require to send an email to there recipient, according to recipient's preferences
     *
     * @return Collection
     * @throws \Exception
     */
    public function getNotifiablesWithEmailableNotification()
    {
        $qb = $this->notifiableEntityRepository->createQueryBuilder("ne");
        $qb->from(BaseUser::class, 'u');
        $qb->join('ne.notifiableNotifications', 'nn')
            ->join('nn.notification', 'n', Expr\Join::WITH, ':select_interval_start <= n.date AND n.date < :select_interval_end')
            ->join('u.preferences', 'p')
            ->select('ne as notifiableEntity')
            ->addSelect('nn')
            ->addSelect('n')
            ->addSelect('u.id as recipient_id', 'u.email as recipient_email', 'u.userProfile.firstName as recipient_firstname', 'u.userProfile.lastName as recipient_lastname')
            ->groupBy('ne')
            ->addGroupBy('nn')
            ->addGroupBy('recipient_id', 'recipient_id', 'recipient_firstname', 'recipient_lastname')
            ->where($qb->expr()->andX(
                $qb->expr()->gte('n.date', ':select_interval_start'),
                $qb->expr()->lt('n.date', ':select_interval_end')
            ))
            ->andWhere('nn.seen = :seen')
            ->andWhere($qb->expr()->eq('u.id', 'ne.identifier'))
            ->andWhere($qb->expr()->andX(
                $qb->expr()->eq('p.likeEmailEnabled', 1),
                $qb->expr()->eq('p.likeEmailFrequency', ':likeEmailFrequency')
            ))
            ->setParameter('seen', 0)
            ->setParameter('likeEmailFrequency', NotificationFrequency::ONCE_A_DAY);;

        $selectIntervalStart = new \DateTime("now");
        $selectIntervalStart->sub(new \DateInterval('PT24H'));
        $qb->setParameter("select_interval_start", $selectIntervalStart);

        $selectIntervalEnd = new \DateTime("now");
        $qb->setParameter("select_interval_end", $selectIntervalEnd);

        return $qb->getQuery()->getResult();
    }
}