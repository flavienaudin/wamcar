<?php

namespace AppBundle\Services\Conversation;

use AppBundle\Doctrine\Entity\ApplicationConversation;
use AppBundle\Doctrine\Repository\DoctrineConversationRepository;
use AppBundle\Doctrine\Repository\DoctrineConversationUserRepository;
use AppBundle\Form\Builder\Conversation\ConversationFromDTOBuilder;
use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Form\DTO\ProContactMessageDTO;
use AppBundle\Services\Picture\PathVehiclePicture;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\Event\MessageCreated;
use Wamcar\Conversation\Message;
use Wamcar\Conversation\MessageLinkPreview;
use Wamcar\Conversation\ProContactMessage;
use Wamcar\Conversation\ProContactMessageRepository;
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
    /** @var ProContactMessageRepository */
    private $proContactMessageRepository;

    public function __construct(
        DoctrineConversationRepository $conversationRepository,
        DoctrineConversationUserRepository $conversationUserRepository,
        MessageBus $eventBus,
        PathVehiclePicture $pathVehiclePicture,
        ProContactMessageRepository $proContactMessageRepository
    )
    {
        $this->conversationRepository = $conversationRepository;
        $this->conversationUserRepository = $conversationUserRepository;
        $this->eventBus = $eventBus;
        $this->pathVehiclePicture = $pathVehiclePicture;
        $this->proContactMessageRepository = $proContactMessageRepository;
    }

    /**
     * @param MessageDTO $messageDTO
     * @param ApplicationConversation|null $conversation
     * @return Conversation
     * @throws \Exception
     */
    public function saveConversation(MessageDTO $messageDTO, ?ApplicationConversation $conversation = null): Conversation
    {
        $conversation = ConversationFromDTOBuilder::buildFromDTO($messageDTO, $conversation);
        $message = new Message(
            $conversation, $messageDTO->user,
            $messageDTO->content,
            $messageDTO->vehicleHeader,
            $messageDTO->vehicle,
            $messageDTO->isFleet,
            $messageDTO->attachments
        );
        $this->treatsMessageLinkPreviews($message);

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

    /**
     * @param ProContactMessageDTO $proContactMessageDTO
     * @return ProContactMessage
     */
    public function saveProContactMessage(ProContactMessageDTO $proContactMessageDTO)
    {
        $proContactMessage = new ProContactMessage(
            $proContactMessageDTO->proUser,
            $proContactMessageDTO->firstname,
            $proContactMessageDTO->lastname,
            $proContactMessageDTO->phonenumber,
            $proContactMessageDTO->email,
            $proContactMessageDTO->message,
            $proContactMessageDTO->vehicle
        );
        $this->proContactMessageRepository->update($proContactMessage);
        return $proContactMessage;
    }

    /**
     * @param SymfonyStyle|null $io
     */
    public function clearAndReloadMessageLinkPreviews(?SymfonyStyle $io)
    {
        $conversations = $this->conversationRepository->findAll();
        /** @var Conversation $conversation */
        foreach ($conversations as $index => $conversation) {

            $messages = $conversation->getMessages();
            if ($io != null) {
                $io->text('Conversation n°' . $index);
                $io->progressStart(count($messages));
            }

            /** @var Message $message */
            foreach ($messages as $message) {
                $message->getLinkPreviews()->clear();
                $this->treatsMessageLinkPreviews($message);
                if ($io != null) {
                    $io->progressAdvance();
                }
            }
            $this->conversationRepository->update($conversation);
            if ($io != null) {
                $io->progressFinish();
            }
        }
    }

    /**
     * @param Message $message
     */
    public function treatsMessageLinkPreviews(Message $message)
    {
        $url_regex = '/(http|https):\/\/(www)?(.*)/i';
        preg_match_all($url_regex, $message->getContent(), $urls, PREG_PATTERN_ORDER);

        foreach ($urls[0] as $url) {
            try {
                $url = $this->checkValues($url);
                $tags = get_meta_tags($url);
                $string = $this->fetch_record($url);
                $linkPreview = new MessageLinkPreview($url);

                /// fecth title
                $title_regex = "/<title>[\s\W]*([^<]*)[\s\W]*<\/title>/im";
                preg_match_all($title_regex, $string, $title, PREG_PATTERN_ORDER);
                if (isset($title[1]) && !empty($title[1][0])) {
                    $linkPreview->setTitle($title[1][0]);
                }
                // fetch images from balise meta (og:image, image, twitter:image,...)
                $metaPropertyImageRegex = '/<meta[^>]*(property|name){1}="[^"]*image"[^>]*content=[\s]*"(\S*)"[^>]*>/';

                preg_match_all($metaPropertyImageRegex, $string, $img);
                $imageUrl = null;
                if (isset($img[2])) {
                    $imageUrl = $img[2][0];
                } elseif (isset($tags) && is_array($tags)) {
                    if (isset($tags['twitter:image'])) {
                        $imageUrl = $tags['twitter:image'];
                    } elseif (isset($tags['image'])) {
                        $imageUrl = $tags['image'];
                    }
                }
                if (!empty($imageUrl) && exif_imagetype($imageUrl) !== false) {
                    // Valid image file
                    $linkPreview->setImage($imageUrl);
                }
                if ($linkPreview->isValid()) {
                    $message->addLinkPreview($linkPreview);
                }
            } catch (\Exception $e) {
                // If error while opening the URL (get_meta_tags or fetch_record().fopen()
                // Then do not block
            }
        }
    }

    private function checkValues($value)
    {
        $value = trim($value);
        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }
        $value = strtr($value, array_flip(get_html_translation_table(HTML_ENTITIES)));
        $value = strip_tags($value);
        $value = htmlspecialchars($value);
        return $value;
    }

    /**
     * @param $path
     * @return string
     * @throws \Exception If the URL can not be opened
     */
    private function fetch_record($path)
    {
        try {
            $file = fopen($path, "r");
            if ($file === FALSE) {
                throw new \Exception('Can not open the URL');
            }
            $data = '';
            while (!feof($file)) {
                $data .= fgets($file, 1024);
            }
            return $data;
        } catch (\Exception $e) {
            // If error while opening the URL (get_meta_tags or fetch_record().fopen()
            // Then do not block
            throw $e;
        }
    }
}
