<?php


namespace Wamcar\User;


use AppBundle\Form\DTO\UserVideosInsertDTO;

abstract class VideosInsert
{

    /** @var string */
    private $id;
    /** @var int */
    private $position;
    /** @var BaseUser */
    private $user;
    /** @var null|string */
    private $title;

    /**
     * VideosInsert constructor.
     * @param BaseUser $user
     * @param UserVideosInsertDTO|null $videosInsertDTO
     */
    public function __construct(BaseUser $user, ?UserVideosInsertDTO $videosInsertDTO)
    {
        $this->setUser($user);
        if($videosInsertDTO != null){
            $this->id = $videosInsertDTO->getId();
            $this->setTitle($videosInsertDTO->getTitle());
            $this->setPosition($videosInsertDTO->getPosition());
        }else{
            $this->setPosition(count($user->getVideosInserts()));
        }
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position)
    {
        $this->position = $position;
    }

    /**
     * @return BaseUser|null
     */
    public function getUser(): ?BaseUser
    {
        return $this->user;
    }

    /**
     * @param BaseUser $user
     */
    public function setUser(BaseUser $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}