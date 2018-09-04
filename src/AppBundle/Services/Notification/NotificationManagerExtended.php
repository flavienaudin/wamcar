<?php

namespace AppBundle\Services\Notification;


use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Illuminate\Support\Collection;
use Mgilet\NotificationBundle\Entity\NotifiableNotification;
use Mgilet\NotificationBundle\Entity\Notification;
use Mgilet\NotificationBundle\Entity\Repository\NotifiableNotificationRepository;
use Mgilet\NotificationBundle\Entity\Repository\NotificationRepository;
use Mgilet\NotificationBundle\Manager\NotificationManager;
use Mgilet\NotificationBundle\NotifiableInterface;
use Wamcar\User\BaseLikeVehicle;
use Wamcar\User\PersonalLikeVehicle;
use Wamcar\User\ProLikeVehicle;
use Wamcar\User\UserLikeVehicleRepository;

class NotificationManagerExtended
{

    const NOTIFICATION_LIKE_VEHICLE = BaseLikeVehicle::class;
    const NOTIFICATION_DEFAULT = Notification::class;

    const ORDER_UNSEEN_FIRST = "ORDER_UNSEEN_FIRST";
    const ORDER_DATE_DESC = "ORDER_DATE_DESC";
    const ORDER_DATE_ASC = "ORDER_DATE_ASC ";

    /** @var NotificationManager */
    private $notificationManager;
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
     * Get last 24h notifications
     *
     * @param bool|null $seen
     * @return Collection
     * @throws \Exception
     */
    public function getUnseenNotificationsToSendEmail($seen = null)
    {
        $qb = $this->notificationRepository->createQueryBuilder("n");
        $qb->addSelect('n.notifiableNotifications')
            ->addSelect('nn.notifiableEntity')
            ->join('n.notifiableNotifications', 'nn')
            ->join('nn.notifiableEntity', 'ne')
            ->where($qb->expr()->andX(
                $qb->expr()->gte('n.date', ':select_interval_start'),
                $qb->expr()->lt('n.date', ':select_interval_end')
            ));

        if ($seen !== null) {
            $whereSeen = $seen ? 1 : 0;
            $qb->andWhere('nn.seen = :seen')
                ->setParameter('seen', $whereSeen);
        }

        $selectIntervalStart = new \DateTime("now");
        $selectIntervalStart->sub(new \DateInterval('PT25H'));
        $qb->setParameter("select_interval_start", $selectIntervalStart);

        $selectIntervalEnd = new \DateTime("now");
        $selectIntervalEnd->sub(new \DateInterval('PT24H'));
        $qb->setParameter("select_interval_end", $selectIntervalEnd);

        return $qb->getQuery()->getResult();
    }

}