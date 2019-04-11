<?php

namespace AppBundle\Form\DTO;


use Wamcar\Sale\Declaration;
use Wamcar\User\ProUser;

class SaleDeclarationDTO
{
    /** @var int */
    private $proUserSellerId;
    /** @var string|null */
    private $sellerFirstName;
    /** @var string|null */
    private $sellerLastName;
    /** @var int|null */
    private $leadBuyerId;
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

    /**
     * SaleDeclarationDTO constructor.
     * @param ProUser $proUser
     * @param null|Declaration $declaration
     */
    public function __construct(ProUser $proUser, ?Declaration $declaration)
    {
        if ($declaration != null) {
            $this->proUserSellerId = $declaration->getProUserSeller()->getId();
            $this->sellerFirstName = $declaration->getSellerFirstName();
            $this->sellerLastName = $declaration->getSellerLastName();
            $this->leadBuyerId = ($declaration->getLeadBuyer() != null ? $declaration->getLeadBuyer()->getId() : null);
            $this->buyerLastName = $declaration->getBuyerFirstName();
            $this->buyerFirstName = $declaration->getBuyerLastName();
            $this->transactionSaleAmount = $declaration->getTransactionSaleAmount();
            $this->transactionPartExchangeAmount = $declaration->getTransactionPartExchangeAmount();
            $this->transactionCommentary = $declaration->getTransactionCommentary();
        } else {
            $this->proUserSellerId = $proUser->getId();
            $this->sellerFirstName = $proUser->getFirstName();
            $this->sellerLastName = $proUser->getLastName();
        }
    }


    /**
     * @return int
     */
    public function getProUserSellerId(): int
    {
        return $this->proUserSellerId;
    }

    /**
     * @param int $proUserSellerId
     */
    public function setProUserSellerId(int $proUserSellerId): void
    {
        $this->proUserSellerId = $proUserSellerId;
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
     * @return int|null
     */
    public function getLeadBuyerId(): ?int
    {
        return $this->leadBuyerId;
    }

    /**
     * @param int|null $leadBuyerId
     */
    public function setLeadBuyerId(?int $leadBuyerId): void
    {
        $this->leadBuyerId = $leadBuyerId;
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
}