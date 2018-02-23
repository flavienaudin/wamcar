<?php


namespace AppBundle\Services\Conversation;


use AppBundle\Doctrine\Repository\DoctrineConversationUserRepository;
use AppBundle\Services\User\CanBeInConversation;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wamcar\Conversation\Conversation;
use Wamcar\User\BaseUser;

class ConversationAuthorizationChecker
{
    /** @var TokenStorageInterface  */
    private $tokenStorage;
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;
    /** @var DoctrineConversationUserRepository */
    protected $conversationUserRepository;


    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        DoctrineConversationUserRepository $conversationUserRepository
    )
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->conversationUserRepository = $conversationUserRepository;
    }


    /**
     * @param BaseUser $user
     * @param BaseUser $interlocutor
     */
    public function canCommunicate(BaseUser $user, BaseUser $interlocutor)
    {
        if (!$this->authorizationChecker->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException('Only connected user can create conversation');
        }

        if ($user->isPersonal() && $interlocutor->isPersonal()) {
            throw new AccessDeniedHttpException('You can only communicate with pro user');
        }

        if (!$interlocutor instanceof CanBeInConversation || !$user instanceof CanBeInConversation) {
            throw new AccessDeniedHttpException('Not authorized to communicate');
        }
    }

    public function memberOfConversation(BaseUser $user, Conversation $conversation)
    {
        $conversationUser = $this->conversationUserRepository->findByConversationAndUser($conversation, $user);

        if (!$conversationUser) {
            throw new AccessDeniedHttpException('You are not member of the conversation');
        }
    }

    /**
     * Return the connected user if someone is connected, or throw an exception otherwise
     * @return BaseUser
     * @throws \Exception
     */
    protected function getUser()
    {
        $token = $this->tokenStorage->getToken();
        if (is_object($token->getUser())) {
            return $token->getUser();
        }
        throw new \Exception('User should be logged in');
    }

}
