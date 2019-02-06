<?php

namespace AppBundle\Elasticsearch;

use AppBundle\Elasticsearch\Traits\PersonalProjectIndexerTrait;
use Wamcar\User\Event\PersonalProjectUpdated;
use Wamcar\User\Event\ProjectEvent;
use Wamcar\User\Event\ProjectEventHandler;

class IndexUpdatedPersonalProject implements ProjectEventHandler
{
    use PersonalProjectIndexerTrait;

    /**
     * @param ProjectEvent $event
     */
    public function notify(ProjectEvent $event)
    {
        if (!$event instanceof PersonalProjectUpdated) {
            throw new \InvalidArgumentException("IndexUpdatedPersonalProject can only be notified of 'PersonalProjectUpdated' events");
        }
        $userProject = $event->getProject();
        $this->indexPersonalProject($userProject);
        $this->indexPersonalUserSearchItems($userProject->getPersonalUser());
    }

}
