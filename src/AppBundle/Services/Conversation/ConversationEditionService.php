<?php

namespace AppBundle\Services\Conversation;

use AppBundle\Doctrine\Repository\DoctrineConversationRepository;
use AppBundle\Doctrine\Repository\DoctrineConversationUserRepository;
use AppBundle\Form\Builder\Conversation\ConversationFromDTOBuilder;
use AppBundle\Form\DTO\MessageDTO;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationUser;
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

    /**
     * @param Conversation $conversation
     * @param BaseUser $user
     * @return null|ConversationUser
     */
    public function getConversationUser(Conversation $conversation, BaseUser $user): ?ConversationUser
    {
        /** @var ConversationUser $conversationUser */
        foreach ($conversation->getConversationUsers() as $conversationUser) {
            if ($conversationUser->getUser()->getId() === $user->getId()) {
                return $conversationUser;
            }
        }

        return null;
    }

    /**
     * @param Conversation $conversation
     * @param BaseUser $user
     */
    public function updatePublishedAt(Conversation $conversation, BaseUser $user): void
    {
        $conversationUser = $this->getConversationUser($conversation, $user);

        if ($conversationUser) {
            $conversationUser->setLastOpenedAt(new \DateTime());
            $this->conversationUserRepository->update($conversationUser);
        }
    }
}
