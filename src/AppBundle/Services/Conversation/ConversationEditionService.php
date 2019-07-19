<?php

namespace AppBundle\Services\Conversation;

use AppBundle\Doctrine\Entity\ApplicationConversation;
use AppBundle\Doctrine\Repository\DoctrineConversationRepository;
use AppBundle\Doctrine\Repository\DoctrineConversationUserRepository;
use AppBundle\Form\Builder\Conversation\ConversationFromDTOBuilder;
use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Services\Picture\PathVehiclePicture;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\Event\MessageCreated;
use Wamcar\Conversation\Message;
use Wamcar\User\BaseUser;


class ConversationEditionService
{
    /** @var DoctrineConversationRepository */
    protected $conversationRepository;
    /** @var DoctrineConversationUserRepository */
    protected $conversationUserRepository;
    /** @var MessageBus */
    private $eventBus;
    /** @var PathVehiclePicture */
    private $pathVehiclePicture;

    public function __construct(
        DoctrineConversationRepository $conversationRepository,
        DoctrineConversationUserRepository $conversationUserRepository,
        MessageBus $eventBus,
        PathVehiclePicture $pathVehiclePicture
    )
    {
        $this->conversationRepository = $conversationRepository;
        $this->conversationUserRepository = $conversationUserRepository;
        $this->eventBus = $eventBus;
        $this->pathVehiclePicture = $pathVehiclePicture;
    }

    /**
     * @param MessageDTO $messageDTO
     * @param null|ApplicationConversation $conversation
     * @return Conversation
     */
    public function saveConversation(MessageDTO $messageDTO, ?ApplicationConversation $conversation = null): Conversation
    {
        $conversation = ConversationFromDTOBuilder::buildFromDTO($messageDTO, $conversation);
        $message = new Message($conversation, $messageDTO->user, $messageDTO->content, $messageDTO->vehicleHeader, $messageDTO->vehicle, $messageDTO->isFleet, $messageDTO->attachments);
        $conversation->addMessage($message);

        // Update date conversation
        $conversation->setUpdatedAt(new \DateTime());
        $this->updateLastOpenedAt($conversation, $messageDTO->user);

        $pathImg = null;
        if ($message->getVehicle()) {
            $pathImg = $this->pathVehiclePicture->getPath($message->getVehicle()->getMainPicture(), 'vehicle_mini_thumbnail');
        }

        $updatedConversation = $this->conversationRepository->update($conversation);
        $this->eventBus->handle(new MessageCreated($message, $messageDTO->interlocutor, $pathImg));

        return $updatedConversation;
    }

    /**
     * @param ApplicationConversation $conversation
     * @param BaseUser $user
     */
    public function updateLastOpenedAt(ApplicationConversation $conversation, BaseUser $user): void
    {
        $conversationUser = $this->conversationUserRepository->findByConversationAndUser($conversation, $user);

        if ($conversationUser) {
            $conversationUser->setLastOpenedAt(new \DateTime());
            $this->conversationUserRepository->update($conversationUser);
        }
    }

    /**
     * @param BaseUser $user
     * @param BaseUser $interlocutor
     * @return null|ApplicationConversation
     */
    public function getConversation(BaseUser $user, BaseUser $interlocutor): ?ApplicationConversation
    {
        return $this->conversationRepository->findByUserAndInterlocutor($user, $interlocutor);
    }
}
