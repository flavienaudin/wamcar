<?php


namespace AppBundle\Form\DTO;


use Wamcar\VideoCoaching\VideoProject;

class VideoProjectDTO
{
    /** @var null|string */
    private $title;
    /** @var null|string */
    private $description;

    public static function buildFromVideoProject(VideoProject $videoProject)
    {
        $videoProjectDTO = new VideoProjectDTO();
        $videoProjectDTO->title = $videoProject->getTitle();
        $videoProjectDTO->description = $videoProject->getDescription();
        return $videoProjectDTO;
    }

    /**
     * @return null|string
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

}