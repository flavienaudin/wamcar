<?php

namespace AppBundle\Form\DTO;


use Wamcar\Sale\Declaration;
use Wamcar\User\Lead;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\ProVehicle;

class SaleDeclarationDTO
{

    /** @var ProUser */
    private $proUserSeller;
    /** @var Lead|null */
    private $leadCustomer;
    /** @var string|null */
    private $customerFirstName;
    /** @var string|null */
    private $customerLastName;
    /** @var ProVehicle|null */
    private $proVehicle;
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
        $this->proUserSeller = $proUser;
        if ($declaration != null) {
            $this->leadCustomer = $declaration->getLeadCustomer();
            $this->customerFirstName = $declaration->getCustomerFirstName();
            $this->customerLastName = $declaration->getCustomerLastName();
            $this->proVehicle = $declaration->getProVehicle();
            $this->transactionSaleAmount = $declaration->getTransactionSaleAmount();
            $this->transactionPartExchangeAmount = $declaration->getTransactionPartExchangeAmount();
            $this->transactionCommentary = $declaration->getTransactionCommentary();
        }
    }

    /**
     * @return ProUser
     */
    public function getProUserSeller(): ProUser
    {
        return $this->proUserSeller;
    }

    /**
     * @return Lead|null
     */
    public function getLeadCustomer(): ?Lead
    {
        return $this->leadCustomer;
    }

    /**
     * @param Lead|null $leadCustomer
     */
    public function setLeadCustomer(?Lead $leadCustomer): void
    {
        $this->leadCustomer = $leadCustomer;
    }

    /**
     * @return string|null
     */
    public function getCustomerFirstName(): ?string
    {
        return $this->customerFirstName;
    }

    /**
     * @param string|null $customerFirstName
     */
    public function setCustomerFirstName(?string $customerFirstName): void
    {
        $this->customerFirstName = $customerFirstName;
    }

    /**
     * @return string|null
     */
    public function getCustomerLastName(): ?string
    {
        return $this->customerLastName;
    }

    /**
     * @param string|null $customerLastName
     */
    public function setCustomerLastName(?string $customerLastName): void
    {
        $this->customerLastName = $customerLastName;
    }

    /**
     * @return ProVehicle|null
     */
    public function getProVehicle(): ?ProVehicle
    {
        return $this->proVehicle;
    }

    /**
     * @param ProVehicle|null $proVehicle
     */
    public function setProVehicle(?ProVehicle $proVehicle): void
    {
        $this->proVehicle = $proVehicle;
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
     * @return string|null
     */
    public function getTransactionCommentary(): ?string
    {
        return $this->transactionCommentary;
    }

    /**
     * @param string|null $transactionCommentary
     */
    public function setTransactionCommentary(?string $transactionCommentary): void
    {
        $this->transactionCommentary = $transactionCommentary;
    }
}