<?php

namespace Wamcar\VideoCoaching;


use AppBundle\Doctrine\Entity\FileHolder;
use AppBundle\Doctrine\Entity\FileHolderTrait;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;

class VideoProjectMessageAttachment implements FileHolder
{
    use FileHolderTrait;

    /** @var string */
    private $id;
    /** @var VideoProjectMessage */
    private $videoProjectMessage;

    /**
     * VideoProjectMessageAttachment constructor.
     * @param File $file
     * @param VideoProjectMessage $videoProjectMessage
     * @throws \Exception
     */
    public function __construct(File $file, VideoProjectMessage $videoProjectMessage)
    {
        $this->id = Uuid::uuid4();
        $this->setFile($file);
        $this->videoProjectMessage = $videoProjectMessage;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return VideoProjectMessage
     */
    public function getVideoProjectMessage(): VideoProjectMessage
    {
        return $this->videoProjectMessage;
    }
}