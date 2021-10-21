<?php

namespace AppBundle\Form\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectViewer;

class VideoProjectDocumentDTO
{
    /** @var UploadedFile|null */
    private $file;
    /** @var VideoProject */
    private $videoProject;
    /** @var VideoProjectViewer */
    private $owner;

    public function __construct(VideoProject $videoProject, VideoProjectViewer $owner, ?UploadedFile $file = null)
    {
        $this->videoProject = $videoProject;
        $this->owner = $owner;
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
     * @return VideoProjectViewer
     */
    public function getOwner(): VideoProjectViewer
    {
        return $this->owner;
    }

    /**
     * @param VideoProjectViewer $owner
     */
    public function setOwner(VideoProjectViewer $owner): void
    {
        $this->owner = $owner;
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
