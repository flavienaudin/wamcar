<?php


namespace TypeForm\Doctrine\Entity;


use Ramsey\Uuid\Uuid;
use Wamcar\User\BaseUser;

class AffinityAnswer
{
    /** @var string */
    private $id;
    /** @var BaseUser */
    private $user;
    /** @var string */
    private $token;
    /** @var string */
    private $formId;
    /** @var \DateTime */
    private $submittedAt;
    /** @var string */
    private $content;
    /** @var \DateTime */
    private $treatedAt;

    /**
     * AffinityAnswer constructor.
     * @param BaseUser $user
     * @param string $token
     * @param string $formId
     * @param \DateTime $submittedAt
     * @param string $content
     * @param \DateTime|null $treatedAt
     */
    public function __construct(BaseUser $user, string $token, string $formId, \DateTime $submittedAt, string $content, ?\DateTime $treatedAt = null)
    {

        $this->id = Uuid::uuid4();
        $this->user = $user;
        $this->token = $token;
        $this->formId = $formId;
        $this->submittedAt = $submittedAt;
        $this->content = $content;
        $this->treatedAt = $treatedAt;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return BaseUser
     */
    public function getUser(): BaseUser
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getFormId(): string
    {
        return $this->formId;
    }

    /**
     * @return \DateTime
     */
    public function getSubmittedAt(): \DateTime
    {
        return $this->submittedAt;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getContentAsArray(): array
    {
        return json_decode($this->content, true);
    }

    /**
     * @return \DateTime
     */
    public function getTreatedAt(): \DateTime
    {
        return $this->treatedAt;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @param string $formId
     */
    public function setFormId(string $formId): void
    {
        $this->formId = $formId;
    }

    /**
     * @param \DateTime $submittedAt
     */
    public function setSubmittedAt(\DateTime $submittedAt): void
    {
        $this->submittedAt = $submittedAt;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @param \DateTime|null $treatedAt
     */
    public function setTreatedAt(?\DateTime $treatedAt): void
    {
        $this->treatedAt = $treatedAt;
    }
}