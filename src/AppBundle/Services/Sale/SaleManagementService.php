<?php

namespace AppBundle\Services\Sale;


use AppBundle\Form\DTO\SaleDeclarationDTO;
use Wamcar\Sale\Declaration;
use Wamcar\Sale\SaleDeclarationRepository;
use Wamcar\User\LeadRepository;
use Wamcar\User\ProUser;
use Wamcar\User\UserRepository;

class SaleManagementService
{

    /** @var SaleDeclarationRepository */
    private $saleDeclarationRepository;
    /** @var UserRepository */
    private $userRepository;
    /** @var LeadRepository */
    private $leadRepository;

    /**
     * SaleManagementService constructor.
     * @param SaleDeclarationRepository $saleDeclarationRepository
     * @param UserRepository $userRepository
     * @param LeadRepository $leadRepository
     */
    public function __construct(SaleDeclarationRepository $saleDeclarationRepository, UserRepository $userRepository, LeadRepository $leadRepository)
    {
        $this->saleDeclarationRepository = $saleDeclarationRepository;
        $this->userRepository = $userRepository;
        $this->leadRepository = $leadRepository;
    }

    /**
     * @param ProUser $proUser
     * @param SaleDeclarationDTO $saleDeclarationDTO
     * @param Declaration|null $declaration
     * @return Declaration
     * @throws \Exception
     */
    public function saveSaleDeclaration(ProUser $proUser, SaleDeclarationDTO $saleDeclarationDTO, ?Declaration $declaration = null): Declaration
    {
        if ($declaration == null) {
            $declaration = new Declaration($proUser);
        }
        $declaration->setSellerFirstName($proUser->getFirstName());
        $declaration->setSellerLastName($proUser->getLastName());

        $declaration->setLeadCustomer($saleDeclarationDTO->getLeadCustomer());
        $declaration->setCustomerFirstName($saleDeclarationDTO->getCustomerFirstName());
        $declaration->setCustomerLastName($saleDeclarationDTO->getCustomerLastName());

        $declaration->setProVehicle($saleDeclarationDTO->getProVehicle());
        $declaration->setTransactionSaleAmount($saleDeclarationDTO->getTransactionSaleAmount());
        $declaration->setTransactionPartExchangeAmount($saleDeclarationDTO->getTransactionPartExchangeAmount());
        $declaration->setTransactionCommentary($saleDeclarationDTO->getTransactionCommentary());

        return $this->saleDeclarationRepository->update($declaration);
    }
}