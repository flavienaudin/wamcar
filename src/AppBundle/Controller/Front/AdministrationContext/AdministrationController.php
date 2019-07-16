<?php

namespace AppBundle\Controller\Front\AdministrationContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Services\User\LeadManagementService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdministrationController extends BaseController
{
    /** @var LeadManagementService */
    private $leadManagementService;

    /**
     * AdministrationController constructor.
     * @param LeadManagementService $leadManagementService
     */
    public function __construct(LeadManagementService $leadManagementService)
    {
        $this->leadManagementService = $leadManagementService;
    }

    /**
     * security.yml - access_control : ROLE_ADMIN only
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adminBoardAction()
    {
        return $this->render('front/adminContext/administration_board.html.twig');
    }

    /**
     * security.yml - access_control : ROLE_ADMIN only
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userLinkingAction()
    {
        return $this->render('front/adminContext/user/user_linkings.html.twig');
    }

    /**
     * security.yml - access_control : ROLE_ADMIN only
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userLinkingDataAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        if (!$this->isGranted('ROLE_PRO_ADMIN')) {
            return new JsonResponse(['admin only'], Response::HTTP_UNAUTHORIZED);
        }
        return new JsonResponse($this->leadManagementService->getLeadsAsUserLinkings($request->query->all()));
    }
}