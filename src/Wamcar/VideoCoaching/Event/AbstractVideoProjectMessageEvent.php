<?php

namespace Wamcar\VideoCoaching\Event;


use Wamcar\VideoCoaching\VideoProjectMessage;

abstract class AbstractVideoProjectMessageEvent
{
    /** @var VideoProjectMessage $videoProjectMessage */
    private $videoProjectMessage;

    /**
     * AbstractVideoProjectMessageEvent constructor.
     * @param VideoProjectMessage $videoProjectMessage
     */
    public function __construct(VideoProjectMessage $videoProjectMessage)
    {
        $this->videoProjectMessage = $videoProjectMessage;
    }

    /**
     * @return VideoProjectMessage
     */
    public function getVideoProjectMessage(): VideoProjectMessage
    {
        return $this->videoProjectMessage;
    }
}