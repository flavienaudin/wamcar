<?php


namespace AppBundle\Form\DTO;


use AppBundle\Services\User\CanBeInConversation;
use AppBundle\Session\Model\SessionMessage;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationUser;
use Wamcar\User\BaseUser;
use Wamcar\Vehicle\BaseVehicle;
use Symfony\Component\Validator\Constraints as Assert;

class MessageDTO
{
    /** @var string */
    public $id;
    /** @var  BaseUser */
    public $user;
    /** @var  BaseUser */
    public $interlocutor;
    /**
     * @var  string
     * @Assert\NotBlank()
     */
    public $content;
    /** @var null|BaseVehicle */
    public $vehicleHeader;
    /** @var null|BaseVehicle */
    public $vehicle;
    /** @var bool */
    public $isFleet;

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
    }

    /**
     * @param Conversation $conversation
     * @param BaseUser $user
     * @return MessageDTO
     */
    public static function buildFromConversation(
        Conversation $conversation,
        BaseUser $user
    ): MessageDTO
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
