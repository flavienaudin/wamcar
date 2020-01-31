<?php


namespace AppBundle\Form\DTO;


use Wamcar\User\VideosInsert;

class UserVideosInsertDTO
{
    /** @var int|null */
    private $id;
    /** @var int|null */
    private $position;
    /** @var string|null */
    private $title;

    /**
     * UserVideosInsertDTO constructor.
     */
    public function __construct(VideosInsert $videosInsert)
    {
        $this->id = $videosInsert->getId();
        $this->position= $videosInsert->getPosition();
        $this->title = $videosInsert->getTitle();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int|null $position
     */
    public function setPosition(?int $position)
    {
        $this->position = $position;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     */
    public function setTitle(?string $title)
    {
        $this->title = $title;
    }
}