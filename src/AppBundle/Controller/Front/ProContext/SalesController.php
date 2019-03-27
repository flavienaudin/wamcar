<?php

namespace AppBundle\Controller\Front\ProContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Elastica\VehicleInfoEntityIndexer;
use AppBundle\Security\Voter\ProVehicleVoter;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\Enum\SaleStatus;
use Wamcar\Vehicle\ProVehicle;

class SalesController extends BaseController
{

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var VehicleInfoEntityIndexer */
    private $vehicleInfoEntityIndexer;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;
    /** @var TranslatorInterface $translator */
    private $translator;

    /**
     * SalesController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleInfoEntityIndexer $vehicleInfoEntityIndexer
     * @param ProVehicleEditionService $proVehicleEditionService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleInfoEntityIndexer $vehicleInfoEntityIndexer,
        ProVehicleEditionService $proVehicleEditionService,
        TranslatorInterface $translator
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoEntityIndexer = $vehicleInfoEntityIndexer;
        $this->proVehicleEditionService = $proVehicleEditionService;
        $this->translator = $translator;
    }

    /**
     * @return Response
     */
    public function salesPageAction(){
        $currentUser = $this->getUser();
        if(!$this->isGranted('ROLE_PRO') && !$currentUser instanceof ProUser){
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.warning.sales.unlogged');
            throw new AccessDeniedException();
        }

        $vehiclesToDeclare = $this->proVehicleEditionService->getProUserVehiclesForSalesDeclaration($currentUser);
        $declaredVehicles = $this->proVehicleEditionService->getProUserVehiclesAlreadySalesDeclarated($currentUser);


        return $this->render("front/Seller/sales_declaration.html.twig", [
            "vehiclesToDeclare" => $vehiclesToDeclare,
            "declaredVehicles" => $declaredVehicles
        ]);
    }

    /**
     * @Entity("proVehicle", expr="repository.findIgnoreSoftDeletedOneBy({'id':id})")
     * @param ProVehicle $proVehicle
     * @param string $saleStatus
     * @return Response
     */
    public function declareAction(ProVehicle $proVehicle, string $saleStatus){
        $currentUser = $this->getUser();
        if(!$this->isGranted('ROLE_PRO') && !$currentUser instanceof ProUser){
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.warning.sales.unlogged');
            throw new AccessDeniedException();
        }
        if(!$this->isGranted(ProVehicleVoter::DECLARE, $proVehicle)){
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.warning.sales.unauthorized_to_declare_sale');
            return $this->redirectToRoute("front_pro_user_sales");
        }

        if(!SaleStatus::isValid($saleStatus)){
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.warning.sales.invalid_sale_status_value');
            return $this->redirectToRoute("front_pro_user_sales");
        }

        $this->proVehicleEditionService->updateSaleStatus($proVehicle, new SaleStatus($saleStatus));


        $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.sales.declaration');
        return $this->redirectToRoute("front_pro_user_sales");
    }

}