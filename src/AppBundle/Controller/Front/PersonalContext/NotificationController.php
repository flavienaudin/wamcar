<?php

namespace AppBundle\Controller\Front\PersonalContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use Doctrine\ORM\EntityManagerInterface;
use Mgilet\NotificationBundle\Entity\NotifiableEntity;
use Mgilet\NotificationBundle\Manager\NotificationManager;
use Mgilet\NotificationBundle\NotifiableInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class NotificationController extends BaseController
{

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;
    /** @var NotificationManager $notificationManager */
    private $notificationsManager;

    /**
     * NotificationController constructor.
     * @param EntityManagerInterface $entityManager
     * @param NotificationManager $notificationsManager
     */
    public function __construct(EntityManagerInterface $entityManager, NotificationManager $notificationsManager)
    {
        $this->entityManager = $entityManager;
        $this->notificationsManager = $notificationsManager;
    }


    /**
     * List of all notifications
     *
     * @param NotifiableInterface $notifiable
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function listAction($notifiable)
    {
        /** @var NotifiableEntity $notifiableEntity */
        $notifiableEntity = $this->notificationsManager->getNotifiableEntityById($notifiable);
        if ($notifiableEntity == null) {
            // Get current user notifications
            $notifiableEntity = $this->notificationsManager->getNotifiableEntity($this->getUser());
            if ($notifiableEntity->getId() != $notifiable) {
                // Another notifiable (user) was required => redirect to current user notification instead
                return $this->redirectToRoute("notification_list", ["notifiable" => $notifiableEntity->getId()]);
            }
        }

        // Get the associated Notifiable Entity (user)
        $notifiedEntity = $this->notificationsManager->getNotifiableInterface($notifiableEntity);

        if ($notifiedEntity instanceof ProApplicationUser or $notifiedEntity instanceof PersonalApplicationUser) {
            // Deal with User notifications
            if ($notifiedEntity !== $this->getUser()) {
                // Current user isn't the given notifiable
                /** @var NotifiableEntity $currentUserNotifiable */
                $currentUserNotifiable = $this->notificationsManager->getNotifiableEntity($this->getUser());
                return $this->redirectToRoute("notification_list", ["notifiable" => $currentUserNotifiable->getId()]);
            }

            // Get the type of notifications to list
            $notifiableRepo = $this->entityManager->getRepository('MgiletNotificationBundle:NotifiableNotification');
            $notificationList = $notifiableRepo->findAllForNotifiableId($notifiable);

            return $this->render('front/Notifications/view_all.html.twig', array(
                'notificationList' => $notificationList
            ));
        }
        // No page found to display the notifiable's notifications
        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_DANGER,
            'Unsupported notifiable'
        );
        return $this->redirectToRoute("front_default");
    }

    /**
     * Follow the notification link
     *
     * @param int $notifiableId
     * @param int $notificationId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \LogicException
     */
    public function followLinkAction($notifiableId, $notificationId)
    {
        $notification = $this->notificationsManager->getNotification($notificationId);
        $notifiedEntity = $this->notificationsManager->getNotifiableInterface($this->notificationsManager->getNotifiableEntityById($notifiableId));
        if ($notifiedEntity instanceof ProApplicationUser or $notifiedEntity instanceof PersonalApplicationUser) {
            // Deal with User notifications
            if ($notifiedEntity === $this->getUser()) {
                if(!$this->notificationsManager->isSeen($notifiedEntity, $notification)) {
                    $this->notificationsManager->markAsSeen(
                        $notifiedEntity,
                        $notification,
                        true
                    );
                }
                $link = $this->notificationsManager->getNotification($notification)->getLink();
                return $this->redirect($link);
            }
        }


        // No page found to display the notifiable's notifications
        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_WARNING,
            'flash.error.notification.unauthorized'
        );
        return $this->redirectToRoute("front_default");

    }

    /**
     * Set a Notification as seen
     *
     * @param int $notifiable
     * @param int $notification
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \LogicException
     */
    public function markAsSeenAction($notifiable, $notification)
    {
        $this->notificationsManager->markAsSeen(
            $this->notificationsManager->getNotifiableInterface($this->notificationsManager->getNotifiableEntityById($notifiable)),
            $this->notificationsManager->getNotification($notification),
            true
        );

        return new JsonResponse([
            "notification_id" => $notification,
            "notification_seen" => true
        ]);
    }

    /**
     * Set a Notification as unseen
     *
     * @param int $notifiable
     * @param int $notification
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \LogicException
     */
    public function markAsUnSeenAction($notifiable, $notification)
    {
        $this->notificationsManager->markAsUnseen(
            $this->notificationsManager->getNotifiableInterface($this->notificationsManager->getNotifiableEntityById($notifiable)),
            $this->notificationsManager->getNotification($notification),
            true
        );

        return new JsonResponse([
            "notification_id" => $notification,
            "notification_seen" => false
        ]);
    }

    /**
     * Set all Notifications for a User as seen
     *
     * @param $notifiable
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markAllAsSeenAction($notifiable)
    {
        $this->notificationsManager->markAllAsSeen(
            $this->notificationsManager->getNotifiableInterface($this->notificationsManager->getNotifiableEntityById($notifiable)),
            true
        );

        return new JsonResponse(true);
    }


}