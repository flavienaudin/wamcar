<?php

namespace AppBundle\EntityListener;


use Doctrine\ORM\EntityManagerInterface;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Wamcar\Garage\Event\GarageUpdated;
use Wamcar\Garage\Garage;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    /** @var MessageBus */
    private $messabeBus;
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param MessageBus $messabeBus
     */
    public function __construct(MessageBus $messabeBus, EntityManagerInterface $entityManager)
    {
        $this->messabeBus = $messabeBus;
        $this->entityManager = $entityManager;
    }


    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            'easy_admin.pre_initialize' => ['disableSoftDeletableFilter'],
            'easy_admin.pre_list' => array('disableSoftDeletableFilter'),
            'easy_admin.pre_search' => array('disableSoftDeletableFilter'),
            'easy_admin.post_persist' => array('postPersist'),
            'easy_admin.post_delete' => array('postDelete'),
        );
    }

    public function disableSoftDeletableFilter(GenericEvent $event)
    {
        if ($this->entityManager->getFilters()->isEnabled('softDeleteable')) {
            $this->entityManager->getFilters()->disable('softDeleteable');
        }
    }


    public function postPersist(GenericEvent $event)
    {
        $entity = $event->getSubject();

        switch (true) {
            case $entity instanceof Garage:
                $this->messabeBus->handle(new GarageUpdated($entity));
                break;
            default:
                return;
        }


    }


    public function postDelete(GenericEvent $event)
    {
        $entity = $event->getSubject();

        switch (true) {

            default:
                return;
        }


    }
}