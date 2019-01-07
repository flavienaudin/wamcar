<?php

namespace AppBundle\Doctrine\Entity;


use Mgilet\NotificationBundle\Entity\NotificationInterface;
use Mgilet\NotificationBundle\Model\Notification as NotificationModel;

/**
 * Class EventNotification
 * @package AppBundle\Doctrine\Entity
 */
class EventNotification extends NotificationModel implements NotificationInterface
{
    /**
     *
     * @var string
     */
    private $event;

    /**
     * EventNotification constructor.
     * @param string|null $event
     */
    public function __construct(string $event)
    {
        parent::__construct();
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     */
    public function setEvent(string $event): void
    {
        $this->event = $event;
    }
}