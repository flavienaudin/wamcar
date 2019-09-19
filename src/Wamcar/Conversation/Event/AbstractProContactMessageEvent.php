<?php


namespace Wamcar\Conversation\Event;


use Wamcar\Conversation\ProContactMessage;

class AbstractProContactMessageEvent
{
    /** @var ProContactMessage */
    private $proContactMessage;

    /**
     * AbstractProContactMessageEvent constructor.
     * @param ProContactMessage $proContactMessage
     */
    public function __construct(ProContactMessage $proContactMessage)
    {
        $this->proContactMessage = $proContactMessage;
    }

    /**
     * @return ProContactMessage
     */
    public function getProContactMessage(): ProContactMessage
    {
        return $this->proContactMessage;
    }

    /**
     * @param ProContactMessage $proContactMessage
     */
    public function setProContactMessage(ProContactMessage $proContactMessage): void
    {
        $this->proContactMessage = $proContactMessage;
    }

}