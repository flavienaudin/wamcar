<?php

namespace Wamcar\User;


use Wamcar\Vehicle\BaseVehicle;

abstract class BaseLikeVehicle
{

    /** @var int */
    protected $id;
    /** @®var int|null */
    protected $value;
    /** @var BaseUser */
    protected $user;
    /** @var BaseVehicle */
    protected $vehicle;

    /** @var \DateTimeInterface */
    protected $createdAt;
    /** @var \DateTimeInterface */
    protected $updatedAt;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return BaseUser
     */
    public function getUser(): BaseUser
    {
        return $this->user;
    }

    /**
     * @return BaseVehicle
     */
    public function getVehicle(): BaseVehicle
    {
        return $this->vehicle;
    }
}