<?php


namespace AppBundle\Form\DTO;


use Wamcar\VideoCoaching\ScriptSection;
use Wamcar\VideoCoaching\ScriptVersion;
use Wamcar\VideoCoaching\VideoProjectIteration;

class ScriptVersionDTO
{
    /** @var null|int */
    private $id;
    /** @var VideoProjectIteration */
    private $videoProjectIteration;
    /** @var null|string */
    private $title;
    /** @var ScriptSectionDTO[]|array */
    private $scriptSections;

    /**
     * ScriptVersionDTO constructor.
     * @param VideoProjectIteration $videoProjectIteration
     */
    public function __construct(VideoProjectIteration $videoProjectIteration)
    {
        $this->videoProjectIteration = $videoProjectIteration;
        $this->title = "Script n°" . ($videoProjectIteration->getScriptVersions()->count() + 1);
        $this->scriptSections = [];
    }

    public static function buildFromScriptVersion(ScriptVersion $scriptVersion)
    {
        $scriptVersionDTO = new ScriptVersionDTO($scriptVersion->getVideoProjectIteration());
        $scriptVersionDTO->id = $scriptVersion->getId();
        $scriptVersionDTO->title = $scriptVersion->getTitle();
        /** @var ScriptSection $scriptSection */
        foreach ($scriptVersion->getScriptSections() as $scriptSection) {
            $scriptVersionDTO->addScriptSection(ScriptSectionDTO::buildFromScriptSection($scriptSection));
        }
        return $scriptVersionDTO;
    }

    /**
     * @return VideoProjectIteration
     */
    public function getVideoProjectIteration(): VideoProjectIteration
    {
        return $this->videoProjectIteration;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
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
     * @return ScriptSectionDTO[]|array
     */
    public function getScriptSections()
    {
        return $this->scriptSections;
    }

    /**
     * @param ScriptSectionDTO $scriptSectionDTO
     */
    public function addScriptSection(ScriptSectionDTO $scriptSectionDTO): void
    {
        $this->scriptSections[] = $scriptSectionDTO;
        $scriptSectionDTO->setScriptVersionDTO($this);
    }
}