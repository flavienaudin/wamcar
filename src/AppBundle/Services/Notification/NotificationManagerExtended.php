<?php

namespace AppBundle\Services\Notification;


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
use Mgilet\NotificationBundle\Manager\NotificationManager;
use Mgilet\NotificationBundle\NotifiableInterface;
use Wamcar\User\BaseLikeVehicle;
use Wamcar\User\BaseUser;
use Wamcar\User\PersonalLikeVehicle;
use Wamcar\User\ProLikeVehicle;
use Wamcar\User\UserLikeVehicleRepository;
use Wamcar\Vehicle\Enum\NotificationFrequency;

class NotificationManagerExtended
{

    const NOTIFICATION_LIKE_VEHICLE = BaseLikeVehicle::class;
    const NOTIFICATION_DEFAULT = Notification::class;

    const ORDER_UNSEEN_FIRST = "ORDER_UNSEEN_FIRST";
    const ORDER_DATE_DESC = "ORDER_DATE_DESC";
    const ORDER_DATE_ASC = "ORDER_DATE_ASC ";

    /** @var NotificationManager */
    private $notificationManager;
    /** @var NotifiableRepository $notifiableEntityRepository */
    private $notifiableEntityRepository;
    /** @var NotifiableNotificationRepository $notifiableNotificationRepository */
    private $notifiableNotificationRepository;
    /** @var NotificationRepository $notificationRepository */
    private $notificationRepository;
    /** @var UserLikeVehicleRepository $userLikeVehicleRepository */
    private $userLikeVehicleRepository;

    /**
     * NotificationManagerExtended constructor.
     * @param NotificationManager $notificationManager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(NotificationManager $notificationManager, EntityManagerInterface $entityManager)
    {
        $this->notificationManager = $notificationManager;
        $this->notifiableEntityRepository = $entityManager->getRepository(NotifiableEntity::class);
        $this->notifiableNotificationRepository = $entityManager->getRepository(NotifiableNotification::class);
        $this->notificationRepository = $entityManager->getRepository(Notification::class);
        $this->userLikeVehicleRepository = $entityManager->getRepository(BaseLikeVehicle::class);
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
            $notif = $notification->getNotification();
            switch ($notif->getSubject()) {
                case ProLikeVehicle::class:
                case PersonalLikeVehicle::class:
                    $displayableNotification['notificationType'] = self::NOTIFICATION_LIKE_VEHICLE;
                    $messageData = json_decode($notif->getMessage(), true);
                    $displayableNotification['likeVehicle'] = $this->userLikeVehicleRepository->findOne($messageData['identifier']);
                    break;
                default:
                    $displayableNotification['notificationType'] = self::NOTIFICATION_DEFAULT;
            }
            $displayableNotifications[] = $displayableNotification;
        }

        return $displayableNotifications;
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