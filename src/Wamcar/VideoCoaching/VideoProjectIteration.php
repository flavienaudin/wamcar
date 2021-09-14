<?php


namespace Wamcar\VideoCoaching;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Gedmo\Timestampable\Traits\Timestampable;
use Ramsey\Uuid\Uuid;

class VideoProjectIteration
{

    use SoftDeleteable;
    use Timestampable;

    /** @var string */
    private $id;
    /** @var string */
    private $title;
    /** @var VideoProject */
    private $videoProject;
    /** @var Collection of VideoVersion */
    private $videoVersions;

    /**
     * VideoProjectIteration constructor.
     * @param VideoProject $videoProject
     * @param string $title
     */
    public function __construct(VideoProject $videoProject, string $title)
    {
        $this->id = Uuid::uuid4();
        $this->videoProject = $videoProject;
        $this->title = $title;
        $this->videoVersions = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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
     * @return bool true if this iteration is the last one of its VideoProject
     */
    public function isLastIteration(): bool
    {
        return $this->videoProject->getLastIteration() === $this;
    }

    /**
     * @return Collection
     */
    public function getVideoVersions(): Collection
    {
        return $this->videoVersions;
    }

    /**
     * @param VideoVersion $videoVersion
     */
    public function addVideoVersions(VideoVersion $videoVersion): void
    {
        $this->videoVersions->add($videoVersion);
    }

    /**
     * @param VideoVersion $videoVersion
     */
    public function removeVideoVersions(VideoVersion $videoVersion): void
    {
        $this->videoVersions->removeElement($videoVersion);
    }
}