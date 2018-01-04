<?php

namespace Wamcar\User;

class ProjectParameter
{
    /** @var  null|ProjectType */
    protected $type;
    /** @var null|int */
    protected $budget;
    /** @var  null|string */
    protected $description;

    /**
     * ProjectParameter constructor.
     * @param ProjectType|null $type
     * @param int|null $budget
     * @param string|null $description
     */
    public function __construct(
        ProjectType $type = null,
        int $budget = null,
        string $description = null
    )
    {
        $this->type = $type;
        $this->budget = $budget;
        $this->description = $description;
    }

    /**
     * @return null|ProjectType
     */
    public function getType(): ?ProjectType
    {
        return $this->type;
    }

    /**
     * @return int|null
     */
    public function getBudget(): ?int
    {
        return $this->budget;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }
}
