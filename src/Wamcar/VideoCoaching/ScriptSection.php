<?php


namespace Wamcar\VideoCoaching;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ScriptSection
{

    /** @var int */
    private $id;
    /** @var ScriptSectionType */
    private $type;
    /** @var ScriptVersion */
    private $scriptVersion;
    /** @var int */
    private $position;
    /** @var null|string */
    private $title;
    /** @var Collection of ScriptSequence */
    private $scriptSequences;

    /**
     * ScriptSection constructor.
     */
    public function __construct(ScriptVersion $scriptVersion)
    {
        $this->scriptVersion = $scriptVersion;
        $this->scriptSequences = new ArrayCollection();
    }


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return ScriptSectionType
     */
    public function getType(): ScriptSectionType
    {
        return $this->type;
    }

    /**
     * @param ScriptSectionType $type
     */
    public function setType(ScriptSectionType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return ScriptVersion
     */
    public function getScriptVersion(): ScriptVersion
    {
        return $this->scriptVersion;
    }

    /**
     * @param ScriptVersion $scriptVersion
     */
    public function setScriptVersion(ScriptVersion $scriptVersion): void
    {
        $this->scriptVersion = $scriptVersion;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return Collection
     */
    public function getScriptSequences(): Collection
    {
        return $this->scriptSequences;
    }

    /**
     * @param $id
     * @return ScriptSequence|null
     */
    public function getScriptSequenceById($id): ?ScriptSequence
    {
        /** @var ScriptSequence $scriptSequence */
        foreach ($this->scriptSequences as $scriptSequence) {
            if ($scriptSequence->getId() === $id) {
                return $scriptSequence;
            }
        }
        return null;
    }

    /**
     * @param ScriptSequence $scriptSequence
     */
    public function addScriptSequence(ScriptSequence $scriptSequence): void
    {
        $this->scriptSequences->add($scriptSequence);
    }

    /**
     * @param ScriptSequence $scriptSequence
     */
    public function removeScriptSequence(ScriptSequence $scriptSequence): void
    {
        $this->scriptSequences->removeElement($scriptSequence);
    }
}