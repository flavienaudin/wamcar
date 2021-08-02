<?php

namespace Wamcar\VideoCoaching;


use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Gedmo\Timestampable\Traits\Timestampable;
use Wamcar\User\ProUser;

class VideoProjectMessage
{

    use Timestampable;
    use SoftDeleteable;

    /** @var int */
    private $id;
    /** @var string */
    private $content;
    /** @var ProUser */
    private $author;
    /** @var VideoProject */
    private $videoProject;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContent(): string
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
     * @return ProUser
     */
    public function getAuthor(): ProUser
    {
        return $this->author;
    }

    /**
     * @param ProUser $author
     */
    public function setAuthor(ProUser $author): void
    {
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
     * @param VideoProject $videoProject
     */
    public function setVideoProject(VideoProject $videoProject): void
    {
        $this->videoProject = $videoProject;
    }
}