<?php

namespace Wamcar\Conversation;


use AppBundle\Doctrine\Entity\FileHolder;
use AppBundle\Doctrine\Entity\FileHolderTrait;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;

class MessageAttachment implements FileHolder
{
    use FileHolderTrait;

    /** @var string */
    private $id;
    /** @var Message */
    private $message;

    /**
     * MessageAttachment constructor.
     * @param null $id
     * @param File $file
     * @param Message $message
     * @throws
     */
    public function __construct($id = null, File $file, Message $message)
    {
        $this->id = $id ?: Uuid::uuid4();
        $this->setFile($file);
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }
}