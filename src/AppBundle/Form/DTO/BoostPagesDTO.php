<?php

namespace AppBundle\Form\DTO;


use Wamcar\User\BaseUser;

class BoostPagesDTO
{
    /** @var BaseUser */
    private $user;
    /** @var array */
    private $urls;

    /**
     * BoostPagesDTO constructor.
     * @param BaseUser $user
     */
    public function __construct(BaseUser $user)
    {
        $this->user = $user;
    }

    /**
     * @return BaseUser
     */
    public function getUser(): BaseUser
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
     * @return array
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @param array $urls
     */
    public function setUrls(array $urls): void
    {
        $this->urls = $urls;
    }
}