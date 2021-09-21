<?php


namespace Wamcar\VideoCoaching;


class ScriptSequence
{

    /** @var int */
    private $id;
    /** @var ScriptSection */
    private $scriptSection;
    /** @var int */
    private $position;
    /** @var null|string */
    private $dialogue;
    /** @var null|string */
    private $scene;
    /** @var null|ScriptShotType */
    private $shot;

    /**
     * ScriptSequence constructor.
     * @param ScriptSection $scriptSection
     */
    public function __construct(ScriptSection $scriptSection)
    {
        $this->scriptSection = $scriptSection;
        $this->setPosition($scriptSection->getScriptSequences()->count() + 1);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return ScriptSection
     */
    public function getScriptSection(): ScriptSection
    {
        return $this->scriptSection;
    }

    /**
     * @param ScriptSection $scriptSection
     */
    public function setScriptSection(ScriptSection $scriptSection): void
    {
        $this->scriptSection = $scriptSection;
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
    public function getDialogue(): ?string
    {
        return $this->dialogue;
    }

    /**
     * @param string|null $dialogue
     */
    public function setDialogue(?string $dialogue): void
    {
        $this->dialogue = $dialogue;
    }

    /**
     * @return string|null
     */
    public function getScene(): ?string
    {
        return $this->scene;
    }

    /**
     * @param string|null $scene
     */
    public function setScene(?string $scene): void
    {
        $this->scene = $scene;
    }

    /**
     * @return ScriptShotType|null
     */
    public function getShot(): ?ScriptShotType
    {
        return $this->shot;
    }

    /**
     * @param ScriptShotType|null $shot
     */
    public function setShot(?ScriptShotType $shot): void
    {
        $this->shot = $shot;
    }
}