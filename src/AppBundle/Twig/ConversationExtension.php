<?php


namespace AppBundle\Twig;

use AppBundle\Doctrine\Repository\DoctrineConversationUserRepository;
use AppBundle\Doctrine\Repository\DoctrineMessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\AbstractExtension;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationUser;
use Wamcar\Conversation\Message;
use Wamcar\Conversation\MessageAttachment;
use Wamcar\User\BaseUser;

class ConversationExtension extends AbstractExtension
{
    /** @var DoctrineConversationUserRepository */
    protected $conversationUserRepository;
    /** @var DoctrineMessageRepository */
    protected $messageRepository;
    /** @var UploaderHelper */
    protected $uploaderHelper;

    public function __construct(
        DoctrineConversationUserRepository $conversationUserRepository,
        DoctrineMessageRepository $messageRepository,
        UploaderHelper $uploaderHelper
    )
    {
        $this->conversationUserRepository = $conversationUserRepository;
        $this->messageRepository = $messageRepository;
        $this->uploaderHelper = $uploaderHelper;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getInterlocutorConversation', array($this, 'getInterlocutorConversationFunction')),
            new \Twig_SimpleFunction('getCurrentUserConversation', array($this, 'getCurrentUserConversationFunction')),
            new \Twig_SimpleFunction('getLastMessageConversation', array($this, 'getLastMessageConversationFunction')),
            new \Twig_SimpleFunction('getCountUnreadMessages', array($this, 'getCountUnreadMessagesFunction')),
            new \Twig_SimpleFunction('getAttachmentLink', array($this, 'getAttachmentLinkFunction')),
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

    /**
     * @param BaseUser $user
     * @return int
     * @throws
     */
    public function getCountUnreadMessagesFunction(BaseUser $user): int
    {
        return $this->messageRepository->getCountUnreadMessagesByUser($user);
    }

    /**
     * @param MessageAttachment $attachment
     * @return null|string
     */
    public function getAttachmentLinkFunction(MessageAttachment $attachment, Request $request = null): ?string
    {
        if ($request === null) {
            return $this->uploaderHelper->asset($attachment, 'file');
        } else {
            return $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath()
                . $this->uploaderHelper->asset($attachment, 'file');
        }
    }
}
