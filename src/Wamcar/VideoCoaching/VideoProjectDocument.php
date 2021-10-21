<?php


namespace Wamcar\VideoCoaching;


use AppBundle\Doctrine\Entity\FileHolder;
use AppBundle\Doctrine\Entity\FileHolderTrait;
use Gedmo\Timestampable\Traits\Timestampable;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VideoProjectDocument implements FileHolder
{
    use FileHolderTrait;
    use Timestampable;

    /** @var string */
    private $id;
    /** @var VideoProject $videoProject */
    private $videoProject;

    /**
     * VideoProjectDocument constructor.
     * @param UploadedFile $file
     * @param VideoProject $videoProject
     */
    public function __construct(UploadedFile $file, VideoProject $videoProject)
    {
        $this->id = Uuid::uuid4();
        $this->setFile($file);
        $this->setFileName($file->getFilename());
        $this->setFileMimeType($file->getMimeType());
        $this->setFileOriginalName($file->getClientOriginalName());
        $this->setFileSize($file->getSize());

        $this->videoProject = $videoProject;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return VideoProject
     */
    public function getVideoProject(): VideoProject
    {
        return $this->videoProject;
    }
}
