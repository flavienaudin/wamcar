<?php


namespace Wamcar\VideoCoaching;


use AppBundle\Doctrine\Entity\VideoProjectBanner;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Gedmo\Timestampable\Traits\Timestampable;
use Symfony\Component\HttpFoundation\File\File;
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
    /** @var null|VideoProjectBanner */
    private $banner;
    /** @var null|string */
    private $googleStorageBucketName;
    /** @var Collection of VideoProjectDocument */
    private $documents;
    /** @var Collection of VideoProjectViewer */
    private $viewers;
    /** @var Collection of VideoProjectIteration */
    private $videoProjectIterations;
    /** @var Collection of VideoProjectMessage */
    private $messages;

    /**
     * VideoProject constructor.
     */
    public function __construct()
    {
        $this->viewers = new ArrayCollection();
        $this->videoProjectIterations = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->documents = new ArrayCollection();
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
     * @return VideoProjectBanner|null
     */
    public function getBanner(): ?VideoProjectBanner
    {
        return $this->banner;
    }

    /**
     * @return File|null
     */
    public function getBannerFile(): ?File
    {
        return $this->banner ? $this->banner->getFile() : null;
    }

    /**
     * @param VideoProjectBanner|null $banner
     */
    public function setBanner(?VideoProjectBanner $banner): void
    {
        $this->banner = $banner;
    }

    /**
     * @return string|null
     */
    public function getGoogleStorageBucketName(): ?string
    {
        return $this->googleStorageBucketName;
    }

    /**
     * @param string|null $googleStorageBucketName
     */
    public function setGoogleStorageBucketName(?string $googleStorageBucketName): void
    {
        $this->googleStorageBucketName = $googleStorageBucketName;
    }

    /**
     * @return Collection
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    /**
     * @param VideoProjectDocument $videoProjectDocument
     */
    public function addDocument(VideoProjectDocument $videoProjectDocument)
    {
        $this->documents->add($videoProjectDocument);
    }

    /**
     * @param VideoProjectDocument $videoProjectDocument
     */
    public function removeDocument(VideoProjectDocument $videoProjectDocument)
    {
        $this->documents->removeElement($videoProjectDocument);
    }



    /**
     * @param bool $excludeCreators set to true to get only followers, no creators
     * @return Collection of VideoProjectViewer
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
     * @return VideoProjectViewer|bool false if not found
     */
    public function getViewerInfo(ProUser $proUser)
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
     * @return Collection
     */
    public function getOwners(): Collection
    {
        return $this->viewers->filter(function (VideoProjectViewer $videoProjectViewer) {
            return $videoProjectViewer->isOwner();
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
    public function getVideoProjectIterations(): Collection
    {
        return $this->videoProjectIterations;
    }

    /**
     * @return VideoProjectIteration
     */
    public function getLastIteration(): VideoProjectIteration
    {
        return $this->videoProjectIterations->first();
    }

    /**
     * @param VideoProjectIteration $videoProjectIteration
     */
    public function addVideoProjectIteration(VideoProjectIteration $videoProjectIteration): void
    {
        $this->videoProjectIterations->add($videoProjectIteration);
    }

    /**
     * @param VideoProjectIteration $videoProjectIteration
     */
    public function removeVideoProjectIteration(VideoProjectIteration $videoProjectIteration): void
    {
        $this->videoProjectIterations->removeElement($videoProjectIteration);
    }

    /**
     * @return Collection
     */
    public function getMessages(): Collection
    {
        return $this->messages;
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
