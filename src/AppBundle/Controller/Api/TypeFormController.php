<?php

namespace AppBundle\Controller\Api;


use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
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

    /**
     * @SWG\Post(
     *     path="/typeform/affinity/personal",
     *     summary="Soumettre la réponse au formulaire Wamcar Affinity Particulier",
     *     tags={"user", "submit", "wamcar affinity particulier"},
     *     description="Soumettre la réponse au formulaire Wamcar Affinity Particulier",
     *     operationId="userSubmitPersonalFormAction",
     *     @SWG\Parameter(ref="#/parameters/client_id"),
     *     @SWG\Parameter(ref="#/parameters/secret"),
     *     @SWG\Response(response=200, description="Réponse soumise"),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Une ressource est manquante"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function submitAffinityPersonalFormAction(Request $request): Response
    {
        try {
            if(strpos($request->getContentType(), "json") === false) {
                return new JsonResponse(['errors' => ["message" => "Unexpected content type"]], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
            }
            $requesContent = json_decode($request->getContent(), true);
            if(!is_array($requesContent)){
                return new JsonResponse(['errors' => ["message" => "Unexpected content type"]], Response::HTTP_NO_CONTENT);
            }

            try {
                $this->affinityFormManager->treatPersonalForm($requesContent, $request->getContent());
                return new JsonResponse($requesContent);
            }catch (WrongContentException $e){
                return new JsonResponse(["errors" => ["message" => $e->getMessage()]], Response::HTTP_BAD_REQUEST);
            }
        } catch (UnauthorizedHttpException $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getStatusCode());
        }
    }


    /**
     * @SWG\Post(
     *     path="/typeform/affinity/pro",
     *     summary="Soumettre la réponse au formulaire Wamcar Affinity Professionnel",
     *     tags={"user", "submit", "wamcar affinity professionnel"},
     *     description="Soumettre la réponse au formulaire Wamcar Affinity Professionnel",
     *     operationId="userSubmitProFormAction",
     *     @SWG\Parameter(ref="#/parameters/client_id"),
     *     @SWG\Parameter(ref="#/parameters/secret"),
     *     @SWG\Response(response=200, description="Réponse soumise"),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Une ressource est manquante"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function submitAffinityProFormAction(Request $request): Response
    {
        try {
            $user = $this->getUser();
            if ($user instanceof ProApplicationUser) {
                return new JsonResponse(["ok" => true, 'userId' => $user->getId()], Response::HTTP_OK);
            }
            return new JsonResponse(["errors" => ["message" => "Not pro user"]]);
        } catch (UnauthorizedHttpException $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getStatusCode());
        }
    }
}