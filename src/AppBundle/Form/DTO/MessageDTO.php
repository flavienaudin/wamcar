<?php


namespace AppBundle\Form\DTO;


use AppBundle\Services\User\CanBeInConversation;
use Wamcar\Conversation\Conversation;
use Wamcar\User\BaseUser;

class MessageDTO
{
    /** @var string */
    public $id;
    /** @var  BaseUser */
    public $user;
    /** @var  BaseUser */
    public $conversationUser;
    /** @var  string */
    public $content;

    public function __construct(
        ?Conversation $conversation,
        CanBeInConversation $user,
        CanBeInConversation $conversationUser
    )
    {
        $this->id = $conversation ? $conversation->getId() : null;
        $this->user = $user;
        $this->conversationUser = $conversationUser;
    }
}
