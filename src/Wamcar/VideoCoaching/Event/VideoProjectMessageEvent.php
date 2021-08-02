<?php


namespace Wamcar\VideoCoaching\Event;


use Wamcar\VideoCoaching\VideoProjectMessage;

interface VideoProjectMessageEvent
{

    /**
     * AbstractVideoProjectMessageEvent constructor.
     * @param VideoProjectMessage $videoProjectMessage
     */
    public function __construct(VideoProjectMessage $videoProjectMessage);

    /**
     * @return VideoProjectMessage
     */
    public function getVideoProjectMessage(): VideoProjectMessage;
}
