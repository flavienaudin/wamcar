<?php

namespace Wamcar\User;

use Wamcar\Vehicle\Vehicle;

class User
{
    /** @var int */
    protected $id;
    /** @var string */
    protected $email;
    /** @var  ?Title */
    protected $title;
    /** @var ?string */
    protected $name;
    /** @var ?string */
    protected $phone;
    /** @var  ?City */
    protected $city;
    /** @var  array */
    protected $roles;
    /** @var  \DateTimeInterface */
    protected $createdAt;

    /** @var Vehicle[]|array */
    protected $vehicles;

    /**
     * User constructor.
     * @param string $email
     * @param Title $title
     * @param string|null $name
     * @param string|null $phone
     * @param City|null $city
     * @param array $roles
     * @param \DateTimeInterface|null $createdAt
     * @param Vehicle|null $firstVehicle
     */
    public function __construct(
        string $email,
        Title $title = null,
        string $name = null,
        string $phone = null,
        City $city = null,
        array $roles = ['ROLE_USER'],
        \DateTimeInterface $createdAt = null,
        Vehicle $firstVehicle = null
    )
    {
        $this->email = $email;
        $this->title = $title;
        $this->name = $name;
        $this->phone = $phone;
        $this->city = $city;
        $this->roles = $roles;
        $this->createdAt = $createdAt ?: new \DateTimeImmutable();
        $this->vehicles = $firstVehicle ? [$firstVehicle] : [];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return array|Vehicle[]
     */
    public function getVehicles()
    {
        return $this->vehicles;
    }
}
