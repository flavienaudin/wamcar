<?php

namespace AppBundle\Controller\Front\ProContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\SaleDeclarationDTO;
use AppBundle\Form\Type\SaleDeclarationType;
use AppBundle\Security\Voter\SaleDeclarationVoter;
use AppBundle\Services\Sale\SaleManagementService;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Sale\Declaration;
use Wamcar\User\Lead;
use Wamcar\User\LeadRepository;
use Wamcar\User\ProUser;

class SalesController extends BaseController
{

    /** @var SaleManagementService */
    private $saleManagementService;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;
    /** @var LeadRepository */
    private $leadRepository;
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var TranslatorInterface $translator */
    private $translator;

    /**
     * SalesController constructor.
     * @param SaleManagementService $saleManagementService
     * @param ProVehicleEditionService $proVehicleEditionService
     * @param LeadRepository $leadRepository
     * @param FormFactoryInterface $formFactory
     * @param TranslatorInterface $translator
     */
    public function __construct(SaleManagementService $saleManagementService, ProVehicleEditionService $proVehicleEditionService, LeadRepository $leadRepository, FormFactoryInterface $formFactory, TranslatorInterface $translator)
    {
        $this->saleManagementService = $saleManagementService;
        $this->proVehicleEditionService = $proVehicleEditionService;
        $this->leadRepository = $leadRepository;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
    }

    /**
     * @return Response
     */
    public function salesViewAction()
    {
        $currentUser = $this->getUser();
        if (!$this->isGranted('ROLE_PRO') && !$currentUser instanceof ProUser) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.warning.sales.unlogged');
            throw new AccessDeniedException();
        }


        $vehiclesToDeclare = $this->proVehicleEditionService->getProUserVehiclesForSalesDeclaration($currentUser);
        /* TODO : implémenter un nouveau système de status de vente
        $declaredVehicles = $this->proVehicleEditionService->getProUserVehiclesAlreadySalesDeclarated($currentUser);
        */
        $declaredVehicles = new ArrayCollection();

        return $this->render("front/Seller/sales_declaration.html.twig", [
            "vehiclesToDeclare" => $vehiclesToDeclare,
            "declaredVehicles" => $declaredVehicles
        ]);
    }

    /**
     * @param Request $request
     * @param null|Declaration $declaration
     * @return Response
     */
    public function declareFormAction(Request $request, ?Declaration $declaration = null): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof ProUser) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.warning.sales.unlogged');
            throw new AccessDeniedException();
        }
        if ($declaration != null && !$this->isGranted(SaleDeclarationVoter::EDIT, $declaration)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.warning.sales.unauthorized_to_edit_declaration');
            return $this->redirectToRoute('front_pro_user_leads');
        }

        $saleDeclarationDTO = new SaleDeclarationDTO($currentUser, $declaration);
        if (!empty($leadId = $request->query->get("leadId"))) {
            /** @var null|Lead $lead */
            $lead = $this->leadRepository->find($leadId);
            if($lead != null) {
                $saleDeclarationDTO->setLeadBuyerId($lead->getId());
                $saleDeclarationDTO->setBuyerFirstName($lead->getFirstName());
                $saleDeclarationDTO->setBuyerLastName($lead->getLastName());
            }
        }
        $saleDeclarationForm = $this->formFactory->create(SaleDeclarationType::class, $saleDeclarationDTO);
        $saleDeclarationForm->handleRequest($request);
        if ($saleDeclarationForm->isSubmitted() && $saleDeclarationForm->isValid()) {
            $saleDeclarationDTO = $saleDeclarationForm->getData();
            if($this->saleManagementService->saveSaleDeclaration($saleDeclarationDTO , $declaration)){
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.sales.declaration');
                return $this->redirectToRoute('front_pro_user_leads');
            }else{
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.sale.saving');
            }
        }
        return $this->render("front/Seller/sale_declaration_form.html.twig", [
            'saleDeclarationForm' => $saleDeclarationForm->createView()
        ]);
    }
}