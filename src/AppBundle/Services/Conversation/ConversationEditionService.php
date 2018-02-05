<?php

namespace AppBundle\Services\Conversation;

use AppBundle\Doctrine\Repository\DoctrineConversationRepository;
use AppBundle\Form\Builder\Conversation\ConversationFromDTOBuilder;
use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Services\User\CanBeInConversation;
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
     * @param BaseUser $user
     * @param BaseUser $conversationUser
     * @return bool
     */
    public function canCommunicate(BaseUser $user, BaseUser $conversationUser): bool
    {
        return !$conversationUser instanceof CanBeInConversation && !$user instanceof CanBeInConversation;
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
