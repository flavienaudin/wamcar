<?php

namespace AppBundle\Services\Conversation;

use AppBundle\Doctrine\Entity\ApplicationConversation;
use AppBundle\Doctrine\Repository\DoctrineConversationRepository;
use AppBundle\Doctrine\Repository\DoctrineConversationUserRepository;
use AppBundle\Form\Builder\Conversation\ConversationFromDTOBuilder;
use AppBundle\Form\DTO\MessageDTO;
use Wamcar\Conversation\Conversation;
use Wamcar\User\BaseUser;


class ConversationEditionService
{
    /** @var DoctrineConversationRepository */
    protected $conversationRepository;
    /** @var DoctrineConversationUserRepository */
    protected $conversationUserRepository;

    public function __construct(
        DoctrineConversationRepository $conversationRepository,
        DoctrineConversationUserRepository $conversationUserRepository
    )
    {
        $this->conversationRepository = $conversationRepository;
        $this->conversationUserRepository = $conversationUserRepository;
    }

    /**
     * @param MessageDTO $messageDTO
     * @param null|ApplicationConversation $conversation
     * @return Conversation
     */
    public function saveConversation(MessageDTO $messageDTO, ?ApplicationConversation $conversation = null): Conversation
    {
        $conversation = ConversationFromDTOBuilder::buildFromDTO($messageDTO, $conversation);

        return $this->conversationRepository->update($conversation);
    }

    /**
     * @param ApplicationConversation $conversation
     * @param BaseUser $user
     */
    public function updatePublishedAt(ApplicationConversation $conversation, BaseUser $user): void
    {
        $conversationUser = $this->conversationUserRepository->findByConversationAndUser($conversation, $user);

        if ($conversationUser) {
            $conversationUser->setLastOpenedAt(new \DateTime());
            $this->conversationUserRepository->update($conversationUser);
        }
    }
}
