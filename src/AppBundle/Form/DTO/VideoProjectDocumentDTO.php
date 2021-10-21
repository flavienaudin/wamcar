<?php

namespace AppBundle\Form\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Wamcar\VideoCoaching\VideoProject;

class VideoProjectDocumentDTO
{
    /** @var UploadedFile|null */
    private $file;
    /** @var VideoProject */
    private $videoProject;

    public function __construct(VideoProject $videoProject, ?UploadedFile $file = null)
    {
        $this->videoProject = $videoProject;
        $this->file = $file;
    }

    /**
     * @return VideoProject
     */
    public function getVideoProject(): VideoProject
    {
        return $this->videoProject;
    }

    /**
     * @param VideoProject $videoProject
     */
    public function setVideoProject(VideoProject $videoProject): void
    {
        $this->videoProject = $videoProject;
    }


    /**
     * @return UploadedFile|null
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * @param UploadedFile|null $file
     */
    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }
}
