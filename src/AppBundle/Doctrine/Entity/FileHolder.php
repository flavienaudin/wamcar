<?php

namespace AppBundle\Doctrine\Entity;

use Symfony\Component\HttpFoundation\File\File;

interface FileHolder
{
    /**
     * @param File $image
     */
    public function setFile(File $image): void;

    /**
     * @return File
     */
    public function getFile(): File;

    /**
     * @return string
     */
    public function getFileOriginalName(): string;

    /**
     * @return int
     */
    public function getFileSize(): int;

    /**
     * @return string
     */
    public function getFileMimeType(): string;
}
