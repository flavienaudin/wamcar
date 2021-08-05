<?php

namespace Wamcar\VideoCoaching;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    /** @var Collection|array */
    private $attachments;

    /**
     * VideoProjectMessage constructor.
     * @param string $content
     * @param ProUser $author
     * @param VideoProject $videoProject
     * @param array|Collection $attachments
     * @throws \Exception
     */
    public function __construct(string $content, ProUser $author, VideoProject $videoProject, $attachments)
    {
        $this->content = $content;
        $this->author = $author;
        $this->videoProject = $videoProject;
        $this->attachments = new ArrayCollection();
        if ($attachments != null) {
            foreach ($attachments as $attachment) {
                if (!empty($attachment)) {
                    $this->addAttachment(new VideoProjectMessageAttachment($attachment, $this));
                }
            }
        }
    }


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


    /**
     * @return Collection
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    /**
     * @param VideoProjectMessageAttachment $attachment
     */
    public function addAttachment(VideoProjectMessageAttachment $attachment): void
    {
        if ($attachment->getId()) {
            $this->attachments[] = $attachment;
        }
    }
}