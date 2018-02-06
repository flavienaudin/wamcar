<?php

namespace AppBundle\Services\Conversation;

use AppBundle\Doctrine\Repository\DoctrineConversationRepository;
use AppBundle\Form\Builder\Conversation\ConversationFromDTOBuilder;
use AppBundle\Form\DTO\MessageDTO;
use Wamcar\Conversation\Conversation;


class ConversationEditionService
{
    /** @var DoctrineConversationRepository */
    protected $conversationRepository;

    public function __construct(DoctrineConversationRepository $conversationRepository)
    {
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @param MessageDTO $messageDTO
     * @return Conversation
     */
    public function createConversation(MessageDTO $messageDTO): Conversation
    {
        $conversation = ConversationFromDTOBuilder::buildFromDTO($messageDTO, null);

        return $this->conversationRepository->update($conversation);
    }

}
