<?php


namespace AppBundle\Twig;

use AppBundle\Doctrine\Repository\DoctrineConversationUserRepository;
use AppBundle\Doctrine\Repository\DoctrineMessageRepository;
use AppBundle\Doctrine\Repository\DoctrinePersonalVehicleRepository;
use AppBundle\Doctrine\Repository\DoctrineProVehicleRepository;
use AppBundle\Doctrine\Repository\DoctrineVehicleRepository;
use AppBundle\Services\Vehicle\VehicleRepositoryResolver;
use Twig\Extension\AbstractExtension;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationUser;
use Wamcar\Conversation\Message;
use Wamcar\User\BaseUser;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\Vehicle;

class ConversationExtension extends AbstractExtension
{
    /** @var DoctrineConversationUserRepository */
    protected $conversationUserRepository;
    /** @var DoctrineMessageRepository */
    protected $messageRepository;
    /** @var DoctrineProVehicleRepository */
    protected $proVehicleRepository;
    /** @var DoctrinePersonalVehicleRepository */
    protected $personalVehicleRepository;
    /** @var VehicleRepositoryResolver */
    protected $vehicleRepositoryResolver;

    public function __construct(
        DoctrineConversationUserRepository $conversationUserRepository,
        DoctrineMessageRepository $messageRepository,
        DoctrineProVehicleRepository $proVehicleRepository,
        DoctrinePersonalVehicleRepository $personalVehicleRepository,
        VehicleRepositoryResolver $vehicleRepositoryResolver
    )
    {
        $this->conversationUserRepository = $conversationUserRepository;
        $this->messageRepository = $messageRepository;
        $this->proVehicleRepository = $proVehicleRepository;
        $this->personalVehicleRepository = $personalVehicleRepository;
        $this->vehicleRepositoryResolver = $vehicleRepositoryResolver;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getInterlocutorConversation', array($this, 'getInterlocutorConversationFunction')),
            new \Twig_SimpleFunction('getCurrentUserConversation', array($this, 'getCurrentUserConversationFunction')),
            new \Twig_SimpleFunction('getLastMessageConversation', array($this, 'getLastMessageConversationFunction')),
            new \Twig_SimpleFunction('getVehicle', array($this, 'getVehicleFunction'))
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
     * @param string $vehicleId
     * @param null|BaseUser $user
     * @return null|BaseVehicle
     */
    public function getVehicleFunction(string $vehicleId, ?BaseUser $user = null): ?BaseVehicle
    {
        if ($user) {
            $repo = $this->vehicleRepositoryResolver->getVehicleRepositoryByUser($user);
            return $repo->find($vehicleId);
        }

        $vehicle = $this->proVehicleRepository->find($vehicleId);
        return $vehicle ? $vehicle : $this->personalVehicleRepository->find($vehicleId);
    }
}
