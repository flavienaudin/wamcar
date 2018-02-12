<?php


namespace AppBundle\Session\Model;


use AppBundle\Form\DTO\MessageDTO;

class Message
{
    /** @var string */
    public $route;
    /** @var array */
    public $routeParams;
    /** @var MessageDTO */
    public $messageDTO;

    public function __construct(string $route, array $routeParams, MessageDTO $messageDTO)
    {
        $this->route = $route;
        $this->routeParams = $routeParams;
        $this->messageDTO = $messageDTO;
    }
}
