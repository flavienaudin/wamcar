<?php


namespace Wamcar\VideoCoaching;


use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Wamcar\User\ProUser;

class VideoProjectViewer
{
    use SoftDeleteable;

    /** @var ProUser */
    private $viewer;
    /** @var VideoProject */
    private $videoProject;
    /** @var bool true if the viewer is the owner */
    private $isOwner = false;
    /** @var bool true if the viewer is the creator */
    private $isCreator = false;
    /** @var \DateTime|null */
    private $visitedAt;

    /**
     * VideoProjectViewer constructor.
     * @param VideoProject $videoProject
     * @param ProUser $viewer
     * @param bool $isCreator
     * @param bool $isOwner
     */
    public function __construct(VideoProject $videoProject, ProUser $viewer, bool $isCreator = false, bool $isOwner = false)
    {
        $this->videoProject = $videoProject;
        $this->viewer = $viewer;
        $this->isCreator = $isCreator;
        $this->isOwner = $isOwner;
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
     * @return ProUser
     */
    public function getViewer(): ProUser
    {
        return $this->viewer;
    }

    /**
     * @param ProUser $viewer
     */
    public function setViewer(ProUser $viewer): void
    {
        $this->viewer = $viewer;
    }

    /**
     * @return bool
     */
    public function isOwner(): bool
    {
        return $this->isOwner;
    }

    /**
     * @param bool $isOwner
     */
    public function setIsOwner(bool $isOwner): void
    {
        $this->isOwner = $isOwner;
    }

    /**
     * @return bool
     */
    public function isCreator(): bool
    {
        return $this->isCreator;
    }

    /**
     * @param bool $isCreator
     */
    public function setIsCreator(bool $isCreator): void
    {
        $this->isCreator = $isCreator;
    }

    /**
     * @return \DateTime|null
     */
    public function getVisitedAt(): ?\DateTime
    {
        return $this->visitedAt;
    }

    /**
     * @param \DateTime|null $visitedAt
     */
    public function setVisitedAt(?\DateTime $visitedAt): void
    {
        $this->visitedAt = $visitedAt;
    }

    /**
     * To display the association
     * @return string
     */
    public function __toString()
    {
        return $this->getVideoProject()->getTitle() . '<=>' . $this->viewer->getFullName() . ($this->isCreator ?' (porteur)':'');
    }
}
