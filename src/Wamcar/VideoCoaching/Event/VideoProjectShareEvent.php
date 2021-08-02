<?php


namespace Wamcar\VideoCoaching\Event;


use Wamcar\VideoCoaching\VideoProject;

interface VideoProjectShareEvent
{

    /**
     * VideoProjectShareEvent constructor.
     * @param VideoProject $videoProject
     * @param array $followers array of new followers or failed to create followers
     */
    public function __construct(VideoProject $videoProject, array $followers);

    /**
     * @return VideoProject
     */
    public function getVideoProject(): VideoProject;

    /**
     * @return array
     */
    public function getFollowers(): array;
}