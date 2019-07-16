<?php

namespace AppBundle\Controller\Front\PersonalContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Services\Notification\NotificationManagerExtended;
use Doctrine\ORM\EntityManagerInterface;
use Mgilet\NotificationBundle\Entity\NotifiableEntity;
use Mgilet\NotificationBundle\Manager\NotificationManager;
use Mgilet\NotificationBundle\NotifiableInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends BaseController
{

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;
    /** @var NotificationManager $notificationManager */
    private $notificationsManager;

    /** @var NotificationManagerExtended $notificationsManagerExtended */
    private $notificationsManagerExtended;

    /**
     * NotificationController constructor.
     * @param EntityManagerInterface $entityManager
     * @param NotificationManager $notificationsManager
     * @param NotificationManagerExtended $notificationsManagerExtended
     */
    public function __construct(EntityManagerInterface $entityManager, NotificationManager $notificationsManager, NotificationManagerExtended $notificationsManagerExtended)
    {
        $this->entityManager = $entityManager;
        $this->notificationsManager = $notificationsManager;
        $this->notificationsManagerExtended = $notificationsManagerExtended;
    }


    /**
     * List of all notifications
     * security.yml - access_control : ROLE_USER required
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
            if (!$notifiedEntity->is($this->getUser())) {
                // Current user isn't the given notifiable
                /** @var NotifiableEntity $currentUserNotifiable */
                $currentUserNotifiable = $this->notificationsManager->getNotifiableEntity($this->getUser());
                return $this->redirectToRoute("notification_list", ["notifiable" => $currentUserNotifiable->getId()]);
            }

            // Get the type of notifications to list
            $notificationList = $this->notificationsManagerExtended->getNotifications($notifiedEntity);

            return $this->render('front/Notifications/view_all.html.twig', array(
                'notificationList' => $notificationList
            ));
        }
        // No page found to display the notifiable's notifications
        $this->session->getFlashBag()->add(self::FLASH_LEVEL_DANGER, 'Unsupported notifiable');
        return $this->redirectToRoute("front_default");
    }

    /**
     * Follow the notification link
     * security.yml - access_control : ROLE_USER required
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
            if ($notifiedEntity->is($this->getUser())) {
                if (!$this->notificationsManager->isSeen($notifiedEntity, $notification)) {
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
        $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.notification.unauthorized');
        return $this->redirectToRoute("front_default");

    }

    /**
     * Set a Notification as seen
     * security.yml - access_control : ROLE_USER required
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
     * security.yml - access_control : ROLE_USER required
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
     * security.yml - access_control : ROLE_USER required
     *
     * @param $notifiable
     *
     * @return Response
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markAllAsSeenAction(Request $request, $notifiable)
    {
        $this->notificationsManager->markAllAsSeen(
            $this->notificationsManager->getNotifiableInterface($this->notificationsManager->getNotifiableEntityById($notifiable)),
            true
        );
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(true);
        } else {
            return $this->redirect($this->getReferer($request));
        }
    }


}