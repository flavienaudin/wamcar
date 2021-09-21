<?php


namespace AppBundle\Form\DTO;


use Wamcar\VideoCoaching\ScriptSection;
use Wamcar\VideoCoaching\ScriptSectionType;
use Wamcar\VideoCoaching\ScriptSequence;
use Wamcar\VideoCoaching\ScriptVersion;

class ScriptSectionDTO
{
    /** @var null|int */
    private $id;
    /** @var ScriptSectionType */
    private $type;
    /** @var ScriptVersion */
    private $scriptVersion;
    /** @var ScriptVersionDTO */
    private $scriptVersionDTO;
    /** @var int */
    private $position;
    /** @var ScriptSequenceDTO[]|array */
    private $scriptSequences;

    /**
     * ScriptSectionDTO constructor.
     * @param ScriptVersion $scriptVersion
     */
    public function __construct(ScriptVersion $scriptVersion)
    {
        $this->scriptVersion = $scriptVersion;
        $this->position = $scriptVersion->getScriptSections()->count() + 1;
        $this->scriptSequences = [];
    }

    public static function buildFromScriptSection(ScriptSection $scriptSection): ScriptSectionDTO
    {
        $scriptSectionDTO = new ScriptSectionDTO($scriptSection->getScriptVersion());
        $scriptSectionDTO->setId($scriptSection->getId());
        $scriptSectionDTO->setType($scriptSection->getType());
        $scriptSectionDTO->setPosition($scriptSection->getPosition());

        /** @var ScriptSequence $scriptSequence */
        foreach ($scriptSection->getScriptSequences() as $scriptSequence) {
            $scriptSectionDTO->addScriptSequence(ScriptSequenceDTO::buildFromScriptSequence($scriptSequence));
        }

        return $scriptSectionDTO;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
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
     * @return ScriptVersionDTO
     */
    public function getScriptVersionDTO(): ScriptVersionDTO
    {
        return $this->scriptVersionDTO;
    }

    /**
     * @param ScriptVersionDTO $scriptVersionDTO
     */
    public function setScriptVersionDTO(ScriptVersionDTO $scriptVersionDTO): void
    {
        $this->scriptVersionDTO = $scriptVersionDTO;
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
     * @return ScriptSequenceDTO[]|array
     */
    public function getScriptSequences()
    {
        return $this->scriptSequences;
    }

    /**
     * @param ScriptSequenceDTO $scriptSequence
     */
    public function addScriptSequence(ScriptSequenceDTO $scriptSequence): void
    {
        $this->scriptSequences[] = $scriptSequence;
        $scriptSequence->setScriptSectionDTO($this);
    }

    /**
     * @param ScriptSequenceDTO $scriptSequence
     */
    public function removeScriptSequence(ScriptSequenceDTO $scriptSequence): void
    {
        for ($index = 0; $index > count($this->scriptSequences); $index++) {
            if ($this->scriptSequences[$index]->getId() === $scriptSequence) {
                unset($this->scriptSequences[$index]);
                return;
            }
        }
    }
}