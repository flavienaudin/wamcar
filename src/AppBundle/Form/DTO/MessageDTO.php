<?php


namespace AppBundle\Form\DTO;


use AppBundle\Services\User\CanBeInConversation;
use Symfony\Component\HttpFoundation\File\File;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationUser;
use Wamcar\User\BaseUser;
use Wamcar\Vehicle\BaseVehicle;

class MessageDTO
{
    /** @var string */
    public $id;
    /** @var  BaseUser */
    public $user;
    /** @var  BaseUser */
    public $interlocutor;
    /**  @var  string */
    public $content;
    /** @var null|BaseVehicle */
    public $vehicleHeader;
    /** @var null|BaseVehicle */
    public $vehicle;
    /** @var bool */
    public $isFleet;
    /** @var File[] */
    public $attachments;

    public function __construct(
        ?Conversation $conversation,
        CanBeInConversation $user,
        CanBeInConversation $interlocutor
    )
    {
        $this->id = $conversation ? $conversation->getId() : null;
        $this->user = $user;
        $this->interlocutor = $interlocutor;
        $this->isFleet = false;
        $this->attachments = [];
    }

    /**
     * @param Conversation $conversation
     * @param BaseUser $user
     * @return MessageDTO
     */
    public static function buildFromConversation(Conversation $conversation, BaseUser $user): MessageDTO
    {
        $interlocutor = null;

        /** @var ConversationUser $conversationUser */
        foreach ($conversation->getConversationUsers() as $conversationUser) {
            if ($conversationUser->getUser()->getId() !== $user->getId()) {
                $interlocutor = $conversationUser->getUser();
            }
        }

        return new self($conversation, $user, $interlocutor);
    }
}
