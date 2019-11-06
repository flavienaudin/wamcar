<?php


namespace Wamcar\User;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ProService
{

    /** @var int */
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $slug;
    /** @var Collection */
    private $proUserProServices;

    /**
     * ProService constructor.
     */
    public function __construct()
    {
        $this->proUserProServices = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return Collection
     */
    public function getProUserProServices(): Collection
    {
        return $this->proUserProServices;
    }

    /**
     * @param ProUserProService $proUserProService
     */
    public function addProUserProService(ProUserProService $proUserProService)
    {
        $this->proUserProServices->add($proUserProService);
    }

    /**
     * @param ProUserProService $proUserProService
     */
    public function removeProUserProService(ProUserProService $proUserProService)
    {
        $this->proUserProServices->remove($proUserProService);
    }
}