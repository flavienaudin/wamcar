<?php

namespace AppBundle\Controller\Api;


use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TypeForm\Exception\WrongContentException;
use TypeForm\Services\AffinityFormManager;

class TypeFormController extends BaseController
{

    /** @var AffinityFormManager $affinityFormManager */
    private $affinityFormManager;

    /**
     * TypeFormController constructor.
     * @param AffinityFormManager $affinityFormManager
     */
    public function __construct(AffinityFormManager $affinityFormManager)
    {
        $this->affinityFormManager = $affinityFormManager;
    }

    /**
     * @return ProApplicationUser
     */
    public function getUser(): ApplicationUser
    {
        return $this->session->get('AUTH_USER');
    }

    public function submitAffinityPersonalFormAction(Request $request): Response
    {
        if (strpos($request->getContentType(), "json") === false) {
            return new JsonResponse(['errors' => ["message" => "Unexpected content type"]], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        try {
            $this->affinityFormManager->treatPersonalForm($request->getContent());
            return new JsonResponse(['ok']);
        } catch (WrongContentException $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }

    public function submitAffinityProFormAction(Request $request): Response
    {
        if (strpos($request->getContentType(), "json") === false) {
            return new JsonResponse(['errors' => ["message" => "Unexpected content type"]], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }
        try {
            $this->affinityFormManager->treatProForm($request->getContent());
            return new JsonResponse(['ok']);
        } catch (WrongContentException $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }
}