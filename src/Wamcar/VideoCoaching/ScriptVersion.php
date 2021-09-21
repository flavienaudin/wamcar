<?php


namespace Wamcar\VideoCoaching;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Gedmo\Timestampable\Traits\Timestampable;
use Ramsey\Uuid\Uuid;

class ScriptVersion
{
    use SoftDeleteable;
    use Timestampable;

    /** @var string */
    private $id;
    /** @var string */
    private $title;
    /** @var VideoProjectIteration */
    private $videoProjectIteration;
    /** @var Collection of ScriptSection */
    private $scriptSections;

    /**
     * VideoVersion constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->scriptSections = new ArrayCollection();
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
     * @return VideoProjectIteration
     */
    public function getVideoProjectIteration(): VideoProjectIteration
    {
        return $this->videoProjectIteration;
    }

    /**
     * @param VideoProjectIteration $videoProjectIteration
     */
    public function setVideoProjectIteration(VideoProjectIteration $videoProjectIteration): void
    {
        $this->videoProjectIteration = $videoProjectIteration;
    }

    /**
     * @return bool
     */
    public function isLastScriptVersion(): bool
    {
        return $this->videoProjectIteration->getLastScriptVersion() === $this;
    }

    /**
     * @return Collection
     */
    public function getScriptSections(): Collection
    {
        return $this->scriptSections;
    }

    /**
     * @param $id
     * @return ScriptSection|null
     */
    public function getScriptSectionById($id): ?ScriptSection
    {
        /** @var ScriptSection $scriptSection */
        foreach ($this->scriptSections as $scriptSection) {
            if ($scriptSection->getId() === $id) {
                return $scriptSection;
            }
        }
        return null;
    }

    /**
     * @param ScriptSection $scriptSection
     */
    public function addScriptSection(ScriptSection $scriptSection): void
    {
        $this->scriptSections->add($scriptSection);
    }

    /**
     * @param ScriptSection $scriptSection
     */
    public function removeScriptSection(ScriptSection $scriptSection): void
    {
        $this->scriptSections->removeElement($scriptSection);
    }
}