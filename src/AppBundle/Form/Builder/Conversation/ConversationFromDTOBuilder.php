<?php

namespace AppBundle\Form\Builder\Conversation;


use AppBundle\Doctrine\Entity\ApplicationConversation;
use AppBundle\Form\DTO\MessageDTO;
use Wamcar\Conversation\ConversationUser;
use Wamcar\Conversation\Message;

class ConversationFromDTOBuilder
{
    /**
     * @param MessageDTO $dto
     * @param null|ApplicationConversation $conversation
     * @return ApplicationConversation
     */
    public static function buildFromDTO(MessageDTO $dto, ?ApplicationConversation $conversation): ApplicationConversation
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
            $conversation = self::initializeConversation($dto);
        }

        return $conversation;
    }

    /**
     * @param MessageDTO $dto
     * @return ApplicationConversation
     */
    private static function initializeConversation(MessageDTO $dto): ApplicationConversation
    {
        $conversation = new ApplicationConversation();
        $conversationUserFirst =  new ConversationUser($conversation, $dto->user);
        $conversation->addConversationUser($conversationUserFirst);
        $conversationUserSecond =  new ConversationUser($conversation, $dto->interlocutor);
        $conversation->addConversationUser($conversationUserSecond);

        return $conversation;
    }
}
