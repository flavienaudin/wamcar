<?php

namespace Wamcar\Sale;


use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Ramsey\Uuid\Uuid;
use Wamcar\User\Lead;
use Wamcar\User\ProUser;

class Declaration
{
    use SoftDeleteable;

    /** @var string */
    private $id;
    /** @var ProUser */
    private $proUserSeller;
    /** @var string|null */
    private $sellerFirstName;
    /** @var string|null */
    private $sellerLastName;
    /** @var Lead|null */
    private $leadBuyer;
    /** @var string|null */
    private $buyerFirstName;
    /** @var string|null */
    private $buyerLastName;
    /** @var int|null */
    private $transactionSaleAmount;
    /** @var int|null */
    private $transactionPartExchangeAmount;
    /** @var string|null */
    private $transactionCommentary;
    /** @var int|null */
    private $creditEarned;
    /** @var \DateTimeInterface */
    protected $createdAt;
    /** @var \DateTimeInterface */
    protected $updatedAt;

    /**
     * Declaration constructor.
     * @param ProUser $proUserSeller
     * @throws \Exception
     */
    public function __construct(ProUser $proUserSeller)
    {
        $this->id = Uuid::uuid4();
        $this->proUserSeller = $proUserSeller;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return ProUser
     */
    public function getProUserSeller(): ProUser
    {
        return $this->proUserSeller;
    }

    /**
     * @param ProUser $proUserSeller
     */
    public function setProUserSeller(ProUser $proUserSeller): void
    {
        $this->proUserSeller = $proUserSeller;
    }

    /**
     * @return null|string
     */
    public function getSellerFirstName(): ?string
    {
        return $this->sellerFirstName;
    }

    /**
     * @param null|string $sellerFirstName
     */
    public function setSellerFirstName(?string $sellerFirstName): void
    {
        $this->sellerFirstName = $sellerFirstName;
    }

    /**
     * @return null|string
     */
    public function getSellerLastName(): ?string
    {
        return $this->sellerLastName;
    }

    /**
     * @param null|string $sellerLastName
     */
    public function setSellerLastName(?string $sellerLastName): void
    {
        $this->sellerLastName = $sellerLastName;
    }

    /**
     * @return null|Lead
     */
    public function getLeadBuyer(): ?Lead
    {
        return $this->leadBuyer;
    }

    /**
     * @param null|Lead $leadBuyer
     */
    public function setLeadBuyer(?Lead $leadBuyer): void
    {
        $this->leadBuyer = $leadBuyer;
    }

    /**
     * @return null|string
     */
    public function getBuyerFirstName(): ?string
    {
        return $this->buyerFirstName;
    }

    /**
     * @param null|string $buyerFirstName
     */
    public function setBuyerFirstName(?string $buyerFirstName): void
    {
        $this->buyerFirstName = $buyerFirstName;
    }

    /**
     * @return null|string
     */
    public function getBuyerLastName(): ?string
    {
        return $this->buyerLastName;
    }

    /**
     * @param null|string $buyerLastName
     */
    public function setBuyerLastName(?string $buyerLastName): void
    {
        $this->buyerLastName = $buyerLastName;
    }

    /**
     * @return int|null
     */
    public function getTransactionSaleAmount(): ?int
    {
        return $this->transactionSaleAmount;
    }

    /**
     * @param int|null $transactionSaleAmount
     */
    public function setTransactionSaleAmount(?int $transactionSaleAmount): void
    {
        $this->transactionSaleAmount = $transactionSaleAmount;
    }

    /**
     * @return int|null
     */
    public function getTransactionPartExchangeAmount(): ?int
    {
        return $this->transactionPartExchangeAmount;
    }

    /**
     * @param int|null $transactionPartExchangeAmount
     */
    public function setTransactionPartExchangeAmount(?int $transactionPartExchangeAmount): void
    {
        $this->transactionPartExchangeAmount = $transactionPartExchangeAmount;
    }

    /**
     * @return null|string
     */
    public function getTransactionCommentary(): ?string
    {
        return $this->transactionCommentary;
    }

    /**
     * @param null|string $transactionCommentary
     */
    public function setTransactionCommentary(?string $transactionCommentary): void
    {
        $this->transactionCommentary = $transactionCommentary;
    }

    /**
     * @return int|null
     */
    public function getCreditEarned(): ?int
    {
        return $this->creditEarned;
    }

    /**
     * @param int|null $creditEarned
     */
    public function setCreditEarned(?int $creditEarned): void
    {
        $this->creditEarned = $creditEarned;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface $updatedAt
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

}