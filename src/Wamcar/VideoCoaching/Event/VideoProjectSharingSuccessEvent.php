<?php


namespace Wamcar\VideoCoaching\Event;


use Wamcar\VideoCoaching\VideoProject;

class VideoProjectSharingSuccessEvent extends AbstractVideoProjectEvent implements VideoProjectShareEvent
{

    /** @var array of emails (string) => VideoProjectViewer */
    private $followers;

    /**
     * VideoProjectSuccessEvent constructor.
     * @param VideoProject $videoProject
     * @param array $followers
     */
    public function __construct(VideoProject $videoProject, array $followers)
    {
        parent::__construct($videoProject);
        $this->followers = $followers;
    }

    /**
     * @return array
     */
    public function getFollowers(): array
    {
        return $this->followers;
    }
}