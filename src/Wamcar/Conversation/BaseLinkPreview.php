<?php


namespace Wamcar\Conversation;


abstract class BaseLinkPreview implements LinkPreview
{

    /** @var int */
    protected $id;
    /** @var int */
    protected $linkIndex;
    /** @var null|string */
    protected $url;
    /** @var null|string */
    protected $title;
    /** @var null|string */
    protected $description;
    /** @var null|string (url) */
    protected $image;

    /**
     * BaseLinkPreview constructor.
     * @param string|null $url
     */
    public function __construct(?string $url)
    {
        $this->url = $url;
    }

    /**
     * Return <b>true</b> if the link preview is not empty (almost one data is set) and can be added to its HasLinkPreview
     * @return bool
     */
    public function isValid(): bool
    {
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
    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getDomainUrl(): ?string
    {
        return parse_url($this->url, PHP_URL_HOST);
    }

    /**
     * @param string|null $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
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
}