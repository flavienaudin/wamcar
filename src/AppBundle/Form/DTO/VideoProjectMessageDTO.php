<?php


namespace AppBundle\Form\DTO;


use Symfony\Component\HttpFoundation\File\File;
use Wamcar\User\ProUser;
use Wamcar\VideoCoaching\VideoProject;

class VideoProjectMessageDTO
{

    /** @var VideoProject */
    private $videoProject;
    /** @var ProUser */
    private $author;
    /**  @var null|string */
    private $content;
    /** @var File[] */
    private $attachments;

    /**
     * VideoProjectMessageDTO constructor.
     * @param VideoProject $videoProject
     * @param ProUser $author
     */
    public function __construct(VideoProject $videoProject, ProUser $author)
    {
        $this->videoProject = $videoProject;
        $this->author = $author;
        $this->attachments = [];
    }

    /**
     * @return VideoProject
     */
    public function getVideoProject(): VideoProject
    {
        return $this->videoProject;
    }

    /**
     * @return ProUser
     */
    public function getAuthor(): ProUser
    {
        return $this->author;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return File[]
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param File[] $attachments
     */
    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }
}
