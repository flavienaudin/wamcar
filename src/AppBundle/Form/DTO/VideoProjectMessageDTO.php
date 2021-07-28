<?php


namespace AppBundle\Form\DTO;


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

    /**
     * VideoProjectMessageDTO constructor.
     * @param VideoProject $videoProject
     * @param ProUser $author
     */
    public function __construct(VideoProject $videoProject, ProUser $author)
    {
        $this->videoProject = $videoProject;
        $this->author = $author;
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
}
