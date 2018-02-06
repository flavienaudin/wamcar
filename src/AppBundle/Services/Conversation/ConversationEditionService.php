<?php

namespace AppBundle\Services\Conversation;

use AppBundle\Doctrine\Repository\DoctrineConversationRepository;
use AppBundle\Form\Builder\Conversation\ConversationFromDTOBuilder;
use AppBundle\Form\DTO\MessageDTO;
use Wamcar\Conversation\Conversation;
use Wamcar\User\BaseUser;


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
     * @param null|Conversation $conversation
     * @return Conversation
     */
    public function saveConversation(MessageDTO $messageDTO, ?Conversation $conversation = null): Conversation
    {
        $conversation = ConversationFromDTOBuilder::buildFromDTO($messageDTO, $conversation);

        return $this->conversationRepository->update($conversation);
    }

    /**
     * @param BaseUser $user
     * @param BaseUser $interlocutor
     * @return null|Conversation
     */
    public function getConversation(BaseUser $user, BaseUser $interlocutor): ?Conversation
    {
        return $this->conversationRepository->findByUserAndInterlocutor($user, $interlocutor);
    }
}
