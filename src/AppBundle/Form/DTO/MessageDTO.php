<?php


namespace AppBundle\Form\DTO;


use AppBundle\Services\User\CanBeInConversation;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationUser;
use Wamcar\User\BaseUser;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\ProVehicle;

class MessageDTO
{
    /** @var string */
    public $id;
    /** @var  BaseUser */
    public $user;
    /** @var  BaseUser */
    public $interlocutor;
    /** @var  string */
    public $content;
    /** @var null|ProVehicle */
    public $proVehicleHeader;
    /** @var null|PersonalVehicle */
    public $personalVehicleHeader;

    public function __construct(
        ?Conversation $conversation,
        CanBeInConversation $user,
        CanBeInConversation $interlocutor
    )
    {
        $this->id = $conversation ? $conversation->getId() : null;
        $this->user = $user;
        $this->interlocutor = $interlocutor;
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

    /**
     * @param BaseVehicle $vehicle
     */
    public function assignVehicleHeader(BaseVehicle $vehicle): void
    {
        if ($vehicle instanceof ProVehicle) {
            $this->proVehicleHeader = $vehicle;
        } elseif ($vehicle instanceof PersonalVehicle) {
            $this->personalVehicleHeader = $vehicle;
        }
    }

    /**
     * @return null|BaseVehicle
     */
    public function getVehicleHeader(): ?BaseVehicle
    {
        return $this->personalVehicleHeader ?: $this->proVehicleHeader;
    }
}
