<?php


namespace Wamcar\VideoCoaching;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Gedmo\Timestampable\Traits\Timestampable;
use Wamcar\User\ProUser;

class VideoProject
{

    use SoftDeleteable;
    use Timestampable;

    /** @var int */
    private $id;
    /** @var null|string */
    private $slug;
    /** @var string */
    private $title;
    /** @var null|string */
    private $description;
    /** @var Collection of VideoProjectViewer */
    private $viewers;
    /** @var Collection of VideoVersion */
    private $videoVersions;
    /** @var Collection of VideoProjectMessage */
    private $messages;

    /**
     * VideoProject constructor.
     */
    public function __construct()
    {
        $this->viewers = new ArrayCollection();
        $this->videoVersions = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param null|string $slug
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
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
     * @param bool $excludeCreators set to true to get only followers, no creators
     * @return Collection
     */
    public function getViewers(bool $excludeCreators = false): Collection
    {
        if ($excludeCreators) {
            return $this->viewers->filter(function (VideoProjectViewer $videoProjectViewer) {
                return !$videoProjectViewer->isCreator();
            });
        }
        return $this->viewers;
    }

    /**
     * Return the VideoProjectViewer or null, of the given ProUser
     * @param ProUser $proUser
     * @return null|VideoProjectViewer
     */
    public function getViewerInfo(ProUser $proUser): VideoProjectViewer
    {
        return $this->viewers->filter(function (VideoProjectViewer $videoProjectViewer) use ($proUser) {
            return $videoProjectViewer->getViewer()->is($proUser);
        })->first();
    }


    /**
     * @param Collection $viewers
     */
    public function setViewers(Collection $viewers): void
    {
        $this->viewers = $viewers;
    }

    /**
     * @return Collection
     */
    public function getCreators(): Collection
    {
        return $this->viewers->filter(function (VideoProjectViewer $videoProjectViewer) {
            return $videoProjectViewer->isCreator();
        });
    }

    /**
     * @param VideoProjectViewer $viewer
     */
    public function addViewer(VideoProjectViewer $viewer): void
    {
        $this->viewers->add($viewer);
    }

    /**
     * @param VideoProjectViewer $viewer
     */
    public function removeViewer(VideoProjectViewer $viewer): void
    {
        $this->viewers->removeElement($viewer);
    }

    /**
     * @return Collection
     */
    public function getVideoVersions(): Collection
    {
        return $this->videoVersions;
    }

    /**
     * @param VideoVersion $videoVersion
     */
    public function addVideoVersions(VideoVersion $videoVersion): void
    {
        $this->videoVersions->add($videoVersion);
    }

    /**
     * @param VideoVersion $videoVersion
     */
    public function removeVideoVersions(VideoVersion $videoVersion): void
    {
        $this->videoVersions->removeElement($videoVersion);
    }


    /**
     * @return Collection
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @param VideoVersion $searchedVideoVersion
     * @param bool $olderFirst
     * @return array
     */
    public function getVideoProjectMessagesOfVideoVersion(VideoVersion $searchedVideoVersion, $olderFirst = false): array
    {
        $nextSearchedVideoVersion = $searchedVideoVersion->nextProjectVersion();
        $videoVersionMessages = $this->messages->filter(function (VideoProjectMessage $videoProjectMessage) use ($searchedVideoVersion, $nextSearchedVideoVersion) {
            return $videoProjectMessage->getCreatedAt() > $searchedVideoVersion->getCreatedAt()
                && ($nextSearchedVideoVersion == null || $videoProjectMessage->getCreatedAt() < $nextSearchedVideoVersion->getCreatedAt());
        })->toArray();

        if ($olderFirst) {
            // Messages are already order by createdAt Desc (newer first)
            $videoVersionMessages = array_reverse($videoVersionMessages);
        }

        return $videoVersionMessages;
    }

    /**
     * @param VideoProjectMessage $message
     */
    public function addMessage(VideoProjectMessage $message): void
    {
        $this->messages->add($message);
    }

    /**
     * @param VideoProjectMessage $message
     */
    public function removeMessage(VideoProjectMessage $message): void
    {
        $this->messages->removeElement($message);
    }
}