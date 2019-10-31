<?php

namespace AppBundle\EntityListener;


use AppBundle\Doctrine\Entity\PersonalApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use Doctrine\ORM\EntityManagerInterface;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Wamcar\Garage\Event\GarageUpdated;
use Wamcar\Garage\Garage;
use Wamcar\User\Event\PersonalProjectUpdated;
use Wamcar\User\Event\PersonalUserUpdated;
use Wamcar\User\Event\ProUserUpdated;
use Wamcar\User\ProUserProService;

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
            'easy_admin.post_update' => array('postPersist'),
            'easy_admin.post_persist' => array('postPersist'),
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
            case $entity instanceof PersonalApplicationUser:
                $this->messabeBus->handle(new PersonalUserUpdated($entity));
                if ($entity->getProject() != null) {
                    $this->messabeBus->handle(new PersonalProjectUpdated($entity->getProject()));
                }
                break;
            case $entity instanceof ProApplicationUser:
                $this->messabeBus->handle(new ProUserUpdated($entity));
                break;
            case $entity instanceof ProUserProService:
                $this->messabeBus->handle(new ProUserUpdated($entity->getProUser()));
                break;
        }
    }
}