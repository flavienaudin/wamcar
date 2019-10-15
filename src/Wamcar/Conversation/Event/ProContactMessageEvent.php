<?php


namespace Wamcar\Conversation\Event;


use Wamcar\Conversation\ProContactMessage;

interface ProContactMessageEvent
{
    /**
     * AbstractProContactMessageEvent constructor.
     * @param ProContactMessage $proContactMessage
     */
    public function __construct(ProContactMessage $proContactMessage);

    /**
     * @return ProContactMessage
     */
    public function getProContactMessage(): ProContactMessage;

    /**
     * @param ProContactMessage $proContactMessage
     */
    public function setProContactMessage(ProContactMessage $proContactMessage): void;

}