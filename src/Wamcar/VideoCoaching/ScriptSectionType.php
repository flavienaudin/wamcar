<?php


namespace Wamcar\VideoCoaching;

class ScriptSectionType
{

    /** @var int */
    private $id;
    /** @var string */
    private $name;

    /** @var null|string */
    private $dialogue_label;
    /** @var null|string */
    private $dialogue_placeholder;
    /** @var null|string */
    private $scene_label;
    /** @var null|string */
    private $scene_placeholder;
    /** @var null|string */
    private $shot_label;

    /** @var null|string */
    private $instruction;

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getDialogueLabel(): ?string
    {
        return $this->dialogue_label;
    }

    /**
     * @param string|null $dialogue_label
     */
    public function setDialogueLabel(?string $dialogue_label): void
    {
        $this->dialogue_label = $dialogue_label;
    }

    /**
     * @return string|null
     */
    public function getDialoguePlaceholder(): ?string
    {
        return $this->dialogue_placeholder;
    }

    /**
     * @param string|null $dialogue_placeholder
     */
    public function setDialoguePlaceholder(?string $dialogue_placeholder): void
    {
        $this->dialogue_placeholder = $dialogue_placeholder;
    }

    /**
     * @return string|null
     */
    public function getSceneLabel(): ?string
    {
        return $this->scene_label;
    }

    /**
     * @param string|null $scene_label
     */
    public function setSceneLabel(?string $scene_label): void
    {
        $this->scene_label = $scene_label;
    }

    /**
     * @return string|null
     */
    public function getScenePlaceholder(): ?string
    {
        return $this->scene_placeholder;
    }

    /**
     * @param string|null $scene_placeholder
     */
    public function setScenePlaceholder(?string $scene_placeholder): void
    {
        $this->scene_placeholder = $scene_placeholder;
    }

    /**
     * @return string|null
     */
    public function getShotLabel(): ?string
    {
        return $this->shot_label;
    }

    /**
     * @param string|null $shot_label
     */
    public function setShotLabel(?string $shot_label): void
    {
        $this->shot_label = $shot_label;
    }

    /**
     * @return string|null
     */
    public function getInstruction(): ?string
    {
        return $this->instruction;
    }

    /**
     * @param string|null $instruction
     */
    public function setInstruction(?string $instruction): void
    {
        $this->instruction = $instruction;
    }
}