<?php


namespace AppBundle\Twig;

use AppBundle\Doctrine\Entity\FileHolder;
use AppBundle\Doctrine\Repository\DoctrineConversationUserRepository;
use AppBundle\Doctrine\Repository\DoctrineMessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationUser;
use Wamcar\Conversation\Message;
use Wamcar\User\BaseUser;
use Wamcar\User\PersonalUser;

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
            new TwigFunction('getInterlocutorConversation', array($this, 'getInterlocutorConversationFunction')),
            new TwigFunction('getCurrentUserConversation', array($this, 'getCurrentUserConversationFunction')),
            new TwigFunction('getLastMessageConversation', array($this, 'getLastMessageConversationFunction')),
            new TwigFunction('getCountUnreadMessages', array($this, 'getCountUnreadMessagesFunction')),
            new TwigFunction('getAttachmentLink', array($this, 'getAttachmentLinkFunction')),
            new TwigFunction('getUserContactsOfGarages', array($this, 'getUserContactsOfGaragesFunction')),
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
     * @param FileHolder $attachment
     * @return null|string
     */
    public function getAttachmentLinkFunction(FileHolder $attachment, Request $request = null): ?string
    {
        if ($request === null) {
            return $this->uploaderHelper->asset($attachment, 'file');
        } else {
            return $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath()
                . $this->uploaderHelper->asset($attachment, 'file');
        }
    }

    /**
     * @param PersonalUser $personalUser
     * @param array $garages
     * @return array of ProUser
     */
    public function getUserContactsOfGaragesFunction(PersonalUser $personalUser, array $garages): array
    {
        return array_map(function (ConversationUser $conversationUser) {
            return $conversationUser->getUser();
        }, $this->conversationUserRepository->findContactsOfGarages($personalUser, $garages));
    }
}
