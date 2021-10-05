<?php

namespace AppBundle\Doctrine\Entity;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;
use Wamcar\VideoCoaching\Picture;
use Wamcar\VideoCoaching\VideoProject;

class VideoProjectBanner extends Picture implements ApplicationPicture
{
    use FileHolderTrait;

    /** @var string */
    private $id;

    /**
     * VideoProjectBanner constructor.
     * @param VideoProject $videoProject
     * @param File $file
     * @throws \Exception
     */
    public function __construct(VideoProject $videoProject, File $file)
    {
        parent::__construct($videoProject);
        $this->id = Uuid::uuid4();
        $this->setFile($file);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
