<?php

namespace AppBundle\Services\Sale;


use AppBundle\Form\DTO\SaleDeclarationDTO;
use Wamcar\Sale\Declaration;
use Wamcar\Sale\SaleDeclarationRepository;
use Wamcar\User\Lead;
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
     * @param SaleDeclarationDTO $saleDeclarationDTO
     * @param null|Declaration $declaration
     * @return bool
     */
    public function saveSaleDeclaration(SaleDeclarationDTO $saleDeclarationDTO, ?Declaration $declaration = null): bool
    {
        if ($declaration == null) {
            $proUser = $this->userRepository->findOne($saleDeclarationDTO->getProUserSellerId());
            if ($proUser instanceof ProUser) {
                try {
                    $declaration = new Declaration($proUser);
                    $declaration->setSellerFirstName($proUser->getFirstName());
                    $declaration->setSellerLastName($proUser->getLastName());
                } catch (\Exception $e) {
                    return false;
                }
            } else {
                return false;
            }
        }
        if (!empty($saleDeclarationDTO->getLeadBuyerId())) {
            /** @var Lead|null $lead */
            $lead = $this->leadRepository->find($saleDeclarationDTO->getLeadBuyerId());
            if ($lead != null) {
                $declaration->setLeadBuyer($lead);
            }
        }
        $declaration->setBuyerFirstName($saleDeclarationDTO->getBuyerFirstName());
        $declaration->setBuyerLastName($saleDeclarationDTO->getBuyerLastName());
        $declaration->setTransactionSaleAmount($saleDeclarationDTO->getTransactionSaleAmount());
        $declaration->setTransactionPartExchangeAmount($saleDeclarationDTO->getTransactionPartExchangeAmount());
        $declaration->setTransactionCommentary($saleDeclarationDTO->getTransactionCommentary());
        $this->saleDeclarationRepository->update($declaration);
        return true;
    }


}