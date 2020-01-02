<?php


namespace Wamcar\Conversation;


class MessageLinkPreview
{

    /** @var int */
    private $id;
    /** @var int */
    private $linkIndex;
    /** @var null|string */
    private $title;
    /** @var null|string */
    private $description;
    /** @var null|string (url)*/
    private $image;
    /** @var Message */
    private $message;

    /**
     * Return <b>true</b> if the link preview is not empty (almost one data is set) and can be added to its Message
     * @return bool
     */
    public function isValid(): bool {
        return !empty($this->title) || !empty($this->description) || !empty($this->image);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getLinkIndex(): int
    {
        return $this->linkIndex;
    }

    /**
     * @param int $linkIndex
     */
    public function setLinkIndex(int $linkIndex): void
    {
        $this->linkIndex = $linkIndex;
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

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     */
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * @param Message $message
     */
    public function setMessage(Message $message): void
    {
        $this->message = $message;
    }
}