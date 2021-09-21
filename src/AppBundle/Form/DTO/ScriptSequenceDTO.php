<?php


namespace AppBundle\Form\DTO;


use Wamcar\VideoCoaching\ScriptSection;
use Wamcar\VideoCoaching\ScriptSequence;
use Wamcar\VideoCoaching\ScriptShotType;

class ScriptSequenceDTO
{

    /** @var ScriptSection */
    private $scriptSection;
    /** @var ScriptSectionDTO */
    private $scriptSectionDTO;
    /** @var int|null */
    private $id;
    /** @var int|null */
    private $position;
    /** @var null|string */
    private $dialogue;
    /** @var null|string */
    private $scene;
    /** @var null|ScriptShotType */
    private $shot;

    /**
     * ScriptSequenceDTO constructor.
     * @param ScriptSection|null $scriptSection
     */
    public function __construct(?ScriptSection $scriptSection = null)
    {
        $this->scriptSection = $scriptSection;
        $this->position = $scriptSection ? $scriptSection->getScriptSequences()->count() + 1 : null;
    }

    public static function buildFromScriptSequence(ScriptSequence $scriptSequence)
    {
        $scriptSequenceDTO = new ScriptSequenceDTO($scriptSequence->getScriptSection());
        $scriptSequenceDTO->setId($scriptSequence->getId());
        $scriptSequenceDTO->setPosition($scriptSequence->getPosition());
        $scriptSequenceDTO->setDialogue($scriptSequence->getDialogue());
        $scriptSequenceDTO->setScene($scriptSequence->getScene());
        $scriptSequenceDTO->setShot($scriptSequence->getShot());
        return $scriptSequenceDTO;
    }

    public static function createScriptSequenceFromDTO(ScriptSequenceDTO $dto): ScriptSequence
    {
        $scriptSequence = new ScriptSequence($dto->scriptSection);
        $scriptSequence->setPosition($dto->getPosition());
        $scriptSequence->setDialogue($dto->getDialogue());
        $scriptSequence->setScene($dto->getScene());
        $scriptSequence->setShot($dto->getShot());
        return $scriptSequence;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->getDialogue()) && empty($this->getScene()) && empty($this->getShot());
    }


    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return ScriptSection
     */
    public function getScriptSection(): ScriptSection
    {
        return $this->scriptSection;
    }

    /**
     * @return ScriptSectionDTO
     */
    public function getScriptSectionDTO(): ScriptSectionDTO
    {
        return $this->scriptSectionDTO;
    }

    /**
     * @param ScriptSectionDTO $scriptSectionDTO
     */
    public function setScriptSectionDTO(ScriptSectionDTO $scriptSectionDTO): void
    {
        $this->scriptSectionDTO = $scriptSectionDTO;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
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