<?php


namespace Wamcar\User;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ProServiceCategory
{

    /** @var int */
    private $id;
    /** @var string */
    private $label;
    /** @var null|string */
    private $description;
    /** @var bool */
    private $choiceMultiple;
    /** @var null|int */
    private $positionMainFilter;
    /** @var null|int */
    private $positionMoreFilter;
    /** @var Collection */
    private $proServices;

    /**
     * ServiceCategory constructor.
     */
    public function __construct()
    {
        $this->choiceMultiple = false;
        $this->proServices = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isChoiceMultiple(): bool
    {
        return $this->choiceMultiple;
    }

    /**
     * @param bool $choiceMultiple
     */
    public function setChoiceMultiple(bool $choiceMultiple): void
    {
        $this->choiceMultiple = $choiceMultiple;
    }

    /**
     * @return int|null
     */
    public function getPositionMainFilter(): ?int
    {
        return $this->positionMainFilter;
    }

    /**
     * @param int|null $positionMainFilter
     */
    public function setPositionMainFilter(?int $positionMainFilter): void
    {
        $this->positionMainFilter = $positionMainFilter;
    }

    /**
     * @return int|null
     */
    public function getPositionMoreFilter(): ?int
    {
        return $this->positionMoreFilter;
    }

    /**
     * @param int|null $positionMoreFilter
     */
    public function setPositionMoreFilter(?int $positionMoreFilter): void
    {
        $this->positionMoreFilter = $positionMoreFilter;
    }

    /**
     * @return Collection
     */
    public function getProServices(): Collection
    {
        return $this->proServices;
    }

    /**
     * @param ProService $proService
     */
    public function addProService(ProService $proService): void
    {
        $this->proServices->add($proService);
    }

    /**
     * @param ProService $proService
     */
    public function removeProService(ProService $proService): void
    {
        $this->proServices->removeElement($proService);
    }

}