<?php


namespace AppBundle\Twig;

use AppBundle\Doctrine\Repository\DoctrineConversationUserRepository;
use AppBundle\Doctrine\Repository\DoctrineMessageRepository;
use Twig\Extension\AbstractExtension;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationUser;
use Wamcar\Conversation\Message;
use Wamcar\User\BaseUser;

class ConversationExtension extends AbstractExtension
{
    /** @var DoctrineConversationUserRepository */
    protected $conversationUserRepository;
    /** @var DoctrineMessageRepository */
    protected $messageRepository;

    public function __construct(
        DoctrineConversationUserRepository $conversationUserRepository,
        DoctrineMessageRepository $messageRepository
    )
    {
        $this->conversationUserRepository = $conversationUserRepository;
        $this->messageRepository = $messageRepository;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getInterlocutorConversation', array($this, 'getInterlocutorConversationFunction')),
            new \Twig_SimpleFunction('getCurrentUserConversation', array($this, 'getCurrentUserConversationFunction')),
            new \Twig_SimpleFunction('getLastMessageConversation', array($this, 'getLastMessageConversationFunction'))
        );
    }

    /**
     * @param Conversation $conversation
     * @param BaseUser $user
     * @return null|ConversationUser
     */
    public function getInterlocutorConversationFunction(Conversation $conversation, BaseUser $user): ?ConversationUser
    {
        return $this->conversationUserRepository->findInterlocutorConversation($conversation, $user);
    }

    /**
     * @param Conversation $conversation
     * @param BaseUser $user
     * @return null|ConversationUser
     */
    public function getCurrentUserConversationFunction(Conversation $conversation, BaseUser $user): ?ConversationUser
    {
        return $this->conversationUserRepository->findByConversationAndUser($conversation, $user);
    }

    /**
     * @param Conversation $conversation
     * @return null|Message
     */
    public function getLastMessageConversationFunction(Conversation $conversation): ?Message
    {
        return $this->messageRepository->getLastConversationMessage($conversation);
    }
}
