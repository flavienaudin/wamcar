<?php

namespace AppBundle\Controller\Api;


use AppBundle\Api\DTO\VehicleDTO;
use AppBundle\Api\DTO\VehicleShortDTO;
use AppBundle\Doctrine\Entity\ProVehiclePicture;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use Psr\Log\LoggerInterface;
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

class VehicleController extends BaseController
{
    private const MAX_IMAGE_UPLOAD = 8;

    /** @var ProVehicleRepository */
    private $vehicleRepository;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;
    /** @var LoggerInterface */
    private $logger;


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
     * @param LoggerInterface $logger
     */
    public function __construct(ProVehicleRepository $vehicleRepository,
                                ProVehicleEditionService $proVehicleEditionService,
                                LoggerInterface $logger)
    {
        $this->vehicleRepository = $vehicleRepository;
        $this->proVehicleEditionService = $proVehicleEditionService;
        $this->logger = $logger;
    }

    public function clearAction(Request $request): Response
    {
        try {
            $nbProVehiclesDeleted = $this->proVehicleEditionService->deleteAllForGarage($this->getGarage());
            return new JsonResponse(["nbVehicleDeleted" => $nbProVehiclesDeleted], Response::HTTP_OK);
        } catch (UnauthorizedHttpException $e) {
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], $e->getStatusCode());
        }
    }

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
            return new JsonResponse(["errors" => ["message" => $e->getMessage()]], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addImageAction(Request $request, string $id): Response
    {
        try {
            $vehicle = $this->getVehicleFromId($id);

            if (count($request->files) > self::MAX_IMAGE_UPLOAD) {
                throw new \InvalidArgumentException(
                    sprintf('You can not upload more than %d images for a vehicle', self::MAX_IMAGE_UPLOAD),
                    Response::HTTP_BAD_REQUEST);

            } elseif (count($request->files) == 0) {
                throw new \InvalidArgumentException('No file received', Response::HTTP_BAD_REQUEST);
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
