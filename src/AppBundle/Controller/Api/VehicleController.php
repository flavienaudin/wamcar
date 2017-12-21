<?php

namespace AppBundle\Controller\Api;


use AppBundle\Services\User\CanBeGarageMember;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Wamcar\Garage\Garage;
use Wamcar\Vehicle\ProVehicleRepository;
use Wamcar\Vehicle\Vehicle;
use Wamcar\Vehicle\VehicleRepository;
use AppBundle\Api\DTO\VehicleDTO;
use Swagger\Annotations as SWG;

/**
 * @SWG\Parameter(parameter="client_id", name="client_id", in="query", description="Votre client ID API", required=true, type="string")
 * @SWG\Parameter(parameter="secret", name="secret", in="header", description="Votre clé secrète", required=true, type="string")
 * @SWG\Parameter(parameter="vehicle_id", name="id", in="path", description="Identifiant unique du véhicule en base d'intégration des stocks", required=true, type="string")
 */
class VehicleController extends BaseController
{
    /** @var ProVehicleRepository */
    private $vehicleRepository;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;

    /**
     * VehicleController constructor.
     * @param ProVehicleRepository $vehicleRepository
     * @param ProVehicleEditionService $proVehicleEditionService
     */
    public function __construct(ProVehicleRepository $vehicleRepository, ProVehicleEditionService $proVehicleEditionService)
    {
        $this->vehicleRepository = $vehicleRepository;
        $this->proVehicleEditionService = $proVehicleEditionService;
    }

    /**
     * @SWG\Delete(
     *     path="/vehicules",
     *     summary="Supprimer tous les véhicules",
     *     tags={"vehicle", "delete", "list"},
     *     description="Supprimer tous les véhicules du professionel",
     *     operationId="vehicleClearAction",
     *     @SWG\Parameter(ref="#/parameters/client_id"),
     *     @SWG\Parameter(ref="#/parameters/secret"),
     *     @SWG\Response(response=200, description="Catalogue véhicules supprimé"),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Une ressource est manquante"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function clearAction(Request $request): Response
    {
        $vehicles = $this->vehicleRepository->findAllForGarage($this->getUserGarage());

        // Todo : batch remove for garage in repository
        foreach ($vehicles as $vehicle) {
            $this->vehicleRepository->remove($vehicle);
        }

        return new JsonResponse(['error' => 0]);
    }

    /**
     * @SWG\Get(
     *     path="/vehicules",
     *     summary="Récupérer tous les vehicules du professionel",
     *     tags={"vehicle", "list"},
     *     description="Récupérer tous les vehicules du professionel (la date de dernière mise à jour ainsi que la liste des photos de chaque véhicule).",
     *     operationId="vehicleListAction",
     *     @SWG\Parameter(ref="#/parameters/client_id"),
     *     @SWG\Parameter(ref="#/parameters/secret"),
     *     @SWG\Response(response=200, description="Les véhicules"),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Une ressource est manquante"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function getListAction(Request $request): Response
    {
        $vehicles = $this->vehicleRepository->findAllForGarage($this->getUserGarage());

        return new JsonResponse($vehicles);
    }

    /**
     * @SWG\Post(
     *     path="/vehicules",
     *     summary="Créer un Vehicule à partir des données soumises",
     *     tags={"vehicle", "create"},
     *     description="Créer un Vehicule à partir des données soumises.",
     *     operationId="vehicleAddAction",
     *     @SWG\Parameter(ref="#/parameters/client_id"),
     *     @SWG\Parameter(ref="#/parameters/secret"),
     *     @SWG\Parameter(in="body", name="body", description="Véhicule à créer", required=true,
     *       @SWG\Schema(
     *          ref="#/definitions/Vehicule",
     *          required={"IdentifiantVehicule", "Date1Mec", "Marque", "Type", "Motorisation", "Modele", "Version", "Energie", "Kilometrage", "PrixVenteTTC", "Description"}
     *       )
     *     ),
     *     @SWG\Response(response=200, description="Les véhicules"),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Une ressource est manquante"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function addAction(Request $request): Response
    {
        if (!$this->getUserGarage()) {
            throw new AccessDeniedHttpException();
        }

        $vehicleDTO = VehicleDTO::createFromJson($request->getContent());
        $this->proVehicleEditionService->createInformations($vehicleDTO, $this->getUserGarage());

        return new Response("Vehicle created", Response::HTTP_OK);
    }

    /**
     * @SWG\Get(
     *     path="/vehicules/{id}",
     *     summary="Récupérer la date de dernière mise à jour ainsi que la liste des photos d'un vehicule.",
     *     tags={"vehicle", "detail"},
     *     description="Récupérer la date de dernière mise à jour ainsi que la liste des photos d'un vehicule.",
     *     operationId="vehicleGetAction",
     *     @SWG\Parameter(ref="#/parameters/client_id"),
     *     @SWG\Parameter(ref="#/parameters/secret"),
     *     @SWG\Parameter(ref="#/parameters/vehicle_id"),
     *     @SWG\Response(response=200, description="Le véhicule"),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Vehicule introuvable"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function getAction(Request $request, string $id): Response
    {
        $vehicle = $this->vehicleRepository->findByReference($id);
        if (!$vehicle) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($vehicle);
    }

    /**
     * @SWG\Delete(
     *     path="/vehicules/{id}",
     *     summary="Supprimer un véhicule.",
     *     tags={"vehicle", "delete"},
     *     description="Supprimer un véhicule.",
     *     operationId="vehicleDeleteAction",
     *     @SWG\Parameter(ref="#/parameters/client_id"),
     *     @SWG\Parameter(ref="#/parameters/secret"),
     *     @SWG\Parameter(ref="#/parameters/vehicle_id"),
     *     @SWG\Response(response=200, description="Vehicule supprimée"),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Vehicule introuvable"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function deleteAction(Request $request, string $id): Response
    {
        $vehicle = $this->vehicleRepository->findByReference($id);
        if (!$vehicle) {
            throw new NotFoundHttpException();
        }


        $this->vehicleRepository->remove($vehicle);

        return new JsonResponse(['error' => 0]);
    }

    /**
     * @SWG\Put(
     *     path="/vehicules/{id}",
     *     summary="Modifier un Vehicule à partir des données soumises",
     *     tags={"vehicle", "edit"},
     *     description="Modifier un Vehicule à partir des données soumises.",
     *     operationId="vehicleEditAction",
     *     @SWG\Parameter(ref="#/parameters/client_id"),
     *     @SWG\Parameter(ref="#/parameters/secret"),
     *     @SWG\Parameter(ref="#/parameters/vehicle_id"),
     *     @SWG\Parameter(in="body", name="body", description="Données du véhicule à modifier", required=true,
     *       @SWG\Schema(
     *          ref="#/definitions/Vehicule",
     *          required={"Date1Mec", "Marque", "Type", "Motorisation", "Modele", "Version", "Energie", "Kilometrage", "PrixVenteTTC", "Description"}
     *       )
     *     ),
     *     @SWG\Response(response=200, description="Véhicule mis à jour"),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Vehicule introuvable"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function editAction(Request $request, string $id): Response
    {
        if (!$this->getUserGarage()) {
            throw new AccessDeniedHttpException();
        }

        $vehicle = $this->vehicleRepository->findByReference($id);
        if (!$vehicle) {
            throw new NotFoundHttpException();
        }

        $vehicleDTO = VehicleDTO::createFromJson($request->getContent());
        $this->proVehicleEditionService->updateInformations($vehicleDTO, $vehicle);

        return new Response("Vehicle updated", Response::HTTP_OK);
    }

    /**
     * @SWG\Post(
     *     path="/vehicules/{id}/images",
     *     summary="Ajouter des images à une voiture",
     *     tags={"vehicle", "images", "add"},
     *     description="Ajouter jusqu'à 8 images à une voiture.",
     *     operationId="vehiclePictureAddAction",
     *     @SWG\Parameter(ref="#/parameters/client_id"),
     *     @SWG\Parameter(ref="#/parameters/secret"),
     *     @SWG\Parameter(ref="#/parameters/vehicle_id"),
     *     @SWG\Parameter(in="body", name="body", description="Collection d'images", required=true,
     *       @SWG\Schema(ref="#/definitions/VehiclePictureCollection")
     *     ),
     *     @SWG\Response(response=200, description="Véhicule mis à jour"),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Une ressource est manquante"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function addImageAction(Request $request, Vehicle $vehicle): Response
    {
        die('addImageAction');
    }

    /**
     * @return Garage
     */
    private function getUserGarage(): Garage
    {
        $user = $this->getUser();
        if (!$user) {
            throw new UnauthorizedHttpException('Newauth realm="use login token".');
        }

        if (!$user instanceof CanBeGarageMember) {
            throw new UnauthorizedHttpException('Newauth realm="not proper user".');
        }

        if (!$user->getGarage()) {
            throw new UnauthorizedHttpException('Newauth realm="create garage first".');
        }

        return $user->getGarage();
    }
}
