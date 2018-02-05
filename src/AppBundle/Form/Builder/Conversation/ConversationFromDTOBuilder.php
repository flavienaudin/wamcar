<?php

namespace AppBundle\Form\Builder\Conversation;


use AppBundle\Form\DTO\MessageDTO;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationUser;
use Wamcar\Conversation\Message;

class ConversationFromDTOBuilder
{
    /**
     * @param MessageDTO $dto
     * @param null|Conversation $conversation
     * @return Conversation
     */
    public static function buildFromDTO(MessageDTO $dto, ?Conversation $conversation): Conversation
    {
        if (!$dto instanceof MessageDTO) {
            throw new \InvalidArgumentException(
                sprintf(
                    "ConversationFromDTOBuilder::buildFromDTO expects dto argument to be an instance of '%s', '%s' given",
                    MessageDTO::class,
                get_class($dto))
            );
        }

        if (!$conversation) {
            $conversation = self::initiliazeConversation($dto);
        }

        $conversation->addMessage(new Message($conversation, $dto->user, $dto->content));

        return $conversation;
    }

    /**
     * @param MessageDTO $dto
     * @return Conversation
     */
    private static function initiliazeConversation(MessageDTO $dto): Conversation
    {
        $conversation = new Conversation();
        $conversationUserFirst =  new ConversationUser($conversation, $dto->user);
        $conversation->addConversationUser($conversationUserFirst);
        $conversationUserSecond =  new ConversationUser($conversation, $dto->interlocutor);
        $conversation->addConversationUser($conversationUserSecond);

        return $conversation;
    }
}
