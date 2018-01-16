<?php

namespace AppBundle\Doctrine\Entity;

use Symfony\Component\HttpFoundation\File\File;

trait FileHolderTrait
{
    /** @var File */
    private $file;
    /** @var string */
    private $fileName;
    /** @var integer */
    private $fileSize;
    /** @var string */
    private $fileMimeType;
    /** @var string */
    private $fileOriginalName;

    /**
     * @return File
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File $file
     */
    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(?string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return int
     */
    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    /**
     * @param int $fileSize
     */
    public function setFileSize(?int $fileSize)
    {
        $this->fileSize = $fileSize;
    }

    /**
     * @return string
     */
    public function getFileMimeType(): ?string
    {
        return $this->fileMimeType;
    }

    /**
     * @param string $fileMimeType
     */
    public function setFileMimeType(?string $fileMimeType)
    {
        $this->fileMimeType = $fileMimeType;
    }

    /**
     * @return string
     */
    public function getFileOriginalName(): ?string
    {
        return $this->fileOriginalName;
    }

    /**
     * @param string $fileOriginalName
     */
    public function setFileOriginalName(?string $fileOriginalName)
    {
        $this->fileOriginalName = $fileOriginalName;
    }
}
