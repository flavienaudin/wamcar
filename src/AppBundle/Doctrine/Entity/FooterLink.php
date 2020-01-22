<?php


namespace AppBundle\Doctrine\Entity;


class FooterLink
{
    /** @var int */
    private $id;
    /** @var int */
    private $columnNumber;
    /** @var int */
    private $position;
    /** @var string */
    private $link;
    /** @var string */
    private $title;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getColumnNumber()
    {
        return $this->columnNumber;
    }

    /**
     * @param int $columnNumber
     */
    public function setColumnNumber(int $columnNumber)
    {
        $this->columnNumber = $columnNumber;
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
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

}