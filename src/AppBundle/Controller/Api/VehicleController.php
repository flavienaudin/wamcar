<?php

namespace AppBundle\Controller\Api;


use AppBundle\Api\DTO\VehicleDTO;
use AppBundle\Api\DTO\VehicleShortDTO;
use AppBundle\Doctrine\Entity\ProVehiclePicture;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use Swagger\Annotations as SWG;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Wamcar\Garage\Garage;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\ProVehicleRepository;

/**
 * @SWG\Parameter(parameter="client_id", name="client_id", in="query", description="Votre client ID API", required=true, type="string")
 * @SWG\Parameter(parameter="secret", name="secret", in="header", description="Votre clé secrète", required=true, type="string")
 * @SWG\Parameter(parameter="vehicle_id", name="id", in="path", description="Identifiant unique du véhicule en base d'intégration des stocks", required=true, type="string")
 */
class VehicleController extends BaseController
{
    private const MAX_IMAGE_UPLOAD = 8;

    /** @var ProVehicleRepository */
    private $vehicleRepository;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;


    /**
     * @return Garage
     */
    public function getGarage(): Garage
    {
        return $this->session->get('AUTH_GARAGE');
    }

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
     *     summary="Supprimer tous les véhicules du garage",
     *     tags={"vehicle", "delete", "list"},
     *     description="Supprimer tous les véhicules du garage",
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
        try {
            $nbProVehiclesDeleted = $this->proVehicleEditionService->deleteAllForGarage($this->getGarage());
            return new JsonResponse(["nbVehicleDeleted" => $nbProVehiclesDeleted], Response::HTTP_OK);
        } catch (UnauthorizedHttpException $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getStatusCode());
        }
    }

    /**
     * @SWG\Get(
     *     path="/vehicules",
     *     summary="Récupérer tous les vehicules du garage",
     *     tags={"vehicle", "list"},
     *     description="Récupérer tous les vehicules du garage (la date de dernière mise à jour ainsi que la liste des photos de chaque véhicule).",
     *     operationId="vehicleListAction",
     *     @SWG\Parameter(ref="#/parameters/client_id"),
     *     @SWG\Parameter(ref="#/parameters/secret"),
     *     @SWG\Response(response=200, description="Les véhicules",
     *       @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/VehicleShort")),
     *     ),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Une ressource est manquante"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function getListAction(Request $request): Response
    {
        try {
            $vehicles = $this->vehicleRepository->findAllForGarage($this->getGarage());

            $data = [];
            /** @var ProVehicle $vehicle */
            foreach ($vehicles as $vehicle) {
                if ($vehicle->getReference()) {
                    $data[$vehicle->getReference()] = VehicleShortDTO::createFromProVehicle($vehicle);
                }
            }

            return new JsonResponse(array_values($data), Response::HTTP_OK);
        } catch (BadRequestHttpException|UnauthorizedHttpException $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getStatusCode());
        } catch (\Exception $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getCode());
        }
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
     *          ref="#/definitions/Vehicle",
     *          required={"IdentifiantVehicule", "Date1Mec", "Marque", "Type", "Motorisation", "Modele", "Version", "Energie", "Kilometrage", "PrixVenteTTC", "Description"}
     *       )
     *     ),
     *     @SWG\Response(response=200, description="Véhicule créé",
     *       @SWG\Schema(ref="#/definitions/VehicleShort")
     *     ),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Une ressource est manquante"),
     *     @SWG\Response(response=409, description="Le véhicule existe déjà"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function addAction(Request $request): Response
    {
        try {
            $vehicleDTO = VehicleDTO::createFromJson($request->getContent());
            $vehicle = $this->vehicleRepository->findByReference($vehicleDTO->IdentifiantVehicule);
            if ($vehicle) {
                throw new ConflictHttpException('reference already used');
            }

            $vehicle = $this->proVehicleEditionService->createInformations($vehicleDTO, $this->getGarage());
            $data = VehicleShortDTO::createFromProVehicle($vehicle);

            return new JsonResponse($data, Response::HTTP_OK);
        } catch (BadRequestHttpException|UnauthorizedHttpException|ConflictHttpException $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getStatusCode(), $e->getHeaders());
        } catch (\Exception $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
     *     @SWG\Response(response=200, description="Le véhicule",
     *       @SWG\Schema(ref="#/definitions/Vehicle")
     *     ),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Vehicule introuvable"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function getAction(Request $request, string $id): Response
    {
        try {
            $vehicleDTO = VehicleDTO::createFromProVehicle($this->getVehicleFromId($id));

            return new JsonResponse($vehicleDTO);
        } catch (BadRequestHttpException|AccessDeniedHttpException|NotFoundHttpException $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getStatusCode());
        } catch (\Exception $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getCode());
        }
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
        try {
            $this->proVehicleEditionService->deleteVehicle($this->getVehicleFromId($id));

            return new JsonResponse();
        } catch (BadRequestHttpException|AccessDeniedHttpException|NotFoundHttpException $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getStatusCode());
        } catch (\Exception $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getCode());
        }
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
     *          ref="#/definitions/Vehicle",
     *          required={"Date1Mec", "Marque", "Type", "Motorisation", "Modele", "Version", "Energie", "Kilometrage", "PrixVenteTTC", "Description"}
     *       )
     *     ),
     *     @SWG\Response(response=200, description="Véhicule mis à jour",
     *       @SWG\Schema(ref="#/definitions/VehicleShort")
     *     ),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Vehicule introuvable"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function editAction(Request $request, string $id): Response
    {
        try {
            $vehicle = $this->getVehicleFromId($id);

            $vehicleDTO = VehicleDTO::createFromJson($request->getContent());
            $vehicle = $this->proVehicleEditionService->updateInformations($vehicleDTO, $vehicle);
            $data = VehicleShortDTO::createFromProVehicle($vehicle);

            return new JsonResponse($data, Response::HTTP_OK);
        } catch (BadRequestHttpException|AccessDeniedHttpException|NotFoundHttpException $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getStatusCode());
        } catch (\Exception $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getCode());
        }
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
     *     @SWG\Response(response=200, description="Véhicule mis à jour",
     *       @SWG\Schema(ref="#/definitions/VehicleShort")
     *     ),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=404, description="Une ressource est manquante"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function addImageAction(Request $request, string $id): Response
    {
        try {
            $vehicle = $this->getVehicleFromId($id);

            if (count($request->files) > self::MAX_IMAGE_UPLOAD) {
                throw new \InvalidArgumentException(sprintf('You can not upload more than %d images for a vehicle', self::MAX_IMAGE_UPLOAD));
            }

            $pictures = [];
            /** @var UploadedFile $file */
            foreach ($request->files as $file) {
                $pictures[] = new ProVehiclePicture(null, $vehicle, $file);
            }

            $vehicle = $this->proVehicleEditionService->addPictures($pictures, $vehicle);
            $data = VehicleShortDTO::createFromProVehicle($vehicle);

            return new JsonResponse($data, Response::HTTP_OK);
        } catch (BadRequestHttpException|AccessDeniedHttpException|NotFoundHttpException $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getStatusCode());
        } catch (\InvalidArgumentException|\Exception $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getCode());
        }
    }

    /**
     * @SWG\Delete(
     *     path="/vehicules/{id}/images",
     *     summary="Retirer les images d'une voiture",
     *     tags={"vehicle", "images", "delete"},
     *     description="Premet de retirer l'intégralité des images d'une voiture.",
     *     operationId="vehiclePicturesRemoveAction",
     *     @SWG\Parameter(ref="#/parameters/client_id"),
     *     @SWG\Parameter(ref="#/parameters/secret"),
     *     @SWG\Parameter(ref="#/parameters/vehicle_id"),
     *     @SWG\Response(response=200, description="Véhicule mis à jour",
     *       @SWG\Schema(ref="#/definitions/VehicleShort")
     *     ),
     *     @SWG\Response(response=401, description="Utilisateur non authentifié"),
     *     @SWG\Response(response=403, description="Accès refusé"),
     *     @SWG\Response(response=400, description="Erreur"),
     * )
     */
    public function removeImagesAction(Request $request, string $id): Response
    {
        try {
            $vehicle = $this->getVehicleFromId($id);
            $vehicle = $this->proVehicleEditionService->removePictures($vehicle);

            $data = VehicleShortDTO::createFromProVehicle($vehicle);

            return new JsonResponse($data, Response::HTTP_OK);
        } catch (BadRequestHttpException|AccessDeniedHttpException|NotFoundHttpException $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getStatusCode());
        } catch (\Exception $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getCode());
        }
    }

    /**
     * @param string $id
     * @return ProVehicle
     * @throws AccessDeniedException|NotFoundHttpException
     */
    private function getVehicleFromId(string $id): ProVehicle
    {
        if (!$this->getGarage()) {
            throw new AccessDeniedHttpException();
        }

        $vehicle = $this->vehicleRepository->findByReference($id);
        if (!$vehicle) {
            throw new NotFoundHttpException();
        }

        if (!$this->getGarage()->hasVehicle($vehicle)) {
            throw new AccessDeniedHttpException();
        }

        return $vehicle;
    }
}
