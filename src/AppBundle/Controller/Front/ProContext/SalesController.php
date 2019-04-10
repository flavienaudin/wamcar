<?php

namespace AppBundle\Controller\Front\ProContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Elasticsearch\Elastica\VehicleInfoEntityIndexer;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\User\ProUser;

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


    public function declareFormAction(Request $request): Response
    {
        return $this->render("front/Seller/sale_declaration_form.html.twig", []);
    }
}