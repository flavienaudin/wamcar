<?php


namespace Wamcar\Conversation;


interface LinkPreview
{

    /**
     * LinkPreview constructor.
     * @param string|null $url
     */
    public function __construct(?string $url);

    /**
     * @param ContentWithLinkPreview $contentWithLinkPreview
     * @return mixed
     */
    public function setOwner(ContentWithLinkPreview $contentWithLinkPreview);

    /**
     * Return <b>true</b> if the link preview is not empty (almost one data is set) and can be added to its HasLinkPreview
     * @return bool
     */
    public function isValid(): bool;

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return int
     */
    public function getLinkIndex(): int;

    /**
     * @param int $linkIndex
     */
    public function setLinkIndex(int $linkIndex): void;

    /**
     * @return string|null
     */
    public function getUrl(): ?string;

    public function getDomainUrl(): ?string;

    /**
     * @param string|null $url
     */
    public function setUrl(?string $url): void;

    /**
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void;

    /**
     * @return string|null
     */
    public function getImage(): ?string;

    /**
     * @param string|null $image
     */
    public function setImage(?string $image): void;
}