<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Controller\Front\SecurityController;
use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Elasticsearch\Elastica\VehicleInfoEntityIndexer;
use AppBundle\Exception\Vehicle\NewSellerToAssignNotFoundException;
use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Form\DTO\ProContactMessageDTO;
use AppBundle\Form\DTO\ProVehicleDTO;
use AppBundle\Form\Type\ContactProType;
use AppBundle\Form\Type\MessageType;
use AppBundle\Form\Type\ProVehicleType;
use AppBundle\Security\Voter\ProVehicleVoter;
use AppBundle\Services\App\CaptchaVerificator;
use AppBundle\Services\Conversation\ConversationAuthorizationChecker;
use AppBundle\Services\Conversation\ConversationEditionService;
use AppBundle\Services\User\CanBeGarageMember;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use AppBundle\Session\SessionMessageManager;
use AutoData\ApiConnector;
use AutoData\Exception\AutodataException;
use AutoData\Exception\AutodataWithUserMessageException;
use AutoData\Request\GetInformationFromPlateNumber;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Conversation\Conversation;
use Wamcar\Conversation\ConversationRepository;
use Wamcar\Conversation\Event\ProContactMessageCreated;
use Wamcar\Garage\Garage;
use Wamcar\User\BaseUser;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\ProVehicle;

class VehicleController extends BaseController
{
    use VehicleTrait;

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var VehicleInfoEntityIndexer */
    private $vehicleInfoEntityIndexer;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;
    /** @var ConversationRepository */
    private $conversationRepository;
    /** @var ConversationAuthorizationChecker */
    protected $conversationAuthorizationChecker;
    /** @var ConversationEditionService */
    protected $conversationEditionService;
    /** @var ApiConnector */
    protected $autoDataConnector;
    /** @var SessionMessageManager */
    protected $sessionMessageManager;
    /** @var TranslatorInterface $translator */
    private $translator;
    /** @var MessageBus */
    protected $eventBus;
    /** @var CaptchaVerificator $captchaService */
    protected $captchaService;

    /**
     * GarageController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleInfoEntityIndexer $vehicleInfoEntityIndexer
     * @param ProVehicleEditionService $proVehicleEditionService
     * @param ConversationRepository $conversationRepository
     * @param ConversationAuthorizationChecker $conversationAuthorizationChecker
     * @param ConversationEditionService $conversationEditionService,
     * @param ApiConnector $autoDataConnector
     * @param SessionMessageManager $sessionMessageManager
     * @param TranslatorInterface $translator
     * @param MessageBus $eventBus
     * @param CaptchaVerificator $captchaService
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleInfoEntityIndexer $vehicleInfoEntityIndexer,
        ProVehicleEditionService $proVehicleEditionService,
        ConversationRepository $conversationRepository,
        ConversationAuthorizationChecker $conversationAuthorizationChecker,
        ConversationEditionService $conversationEditionService,
        ApiConnector $autoDataConnector,
        SessionMessageManager $sessionMessageManager,
        TranslatorInterface $translator,
        MessageBus $eventBus,
        CaptchaVerificator $captchaService
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoEntityIndexer = $vehicleInfoEntityIndexer;
        $this->proVehicleEditionService = $proVehicleEditionService;
        $this->conversationRepository = $conversationRepository;
        $this->conversationAuthorizationChecker = $conversationAuthorizationChecker;
        $this->conversationEditionService = $conversationEditionService;
        $this->autoDataConnector = $autoDataConnector;
        $this->sessionMessageManager = $sessionMessageManager;
        $this->translator = $translator;
        $this->eventBus = $eventBus;
        $this->captchaService = $captchaService;
    }

    /**
     * @param Request $request
     * @param Garage $garage
     * @param string $plateNumber
     * @param ProVehicle $vehicle
     * @Security("has_role('ROLE_USER')")
     * @return Response
     * @throws \AutoData\Exception\AutodataException
     * @ParamConverter("garage", class="Wamcar\Garage\Garage", options={"id" = "garage_id"})
     * @ParamConverter("vehicle", class="Wamcar\Vehicle\ProVehicle", options={"id" = "vehicle_id"})
     */
    public function saveAction(
        Request $request,
        Garage $garage = null,
        ProVehicle $vehicle = null,
        string $plateNumber = null): Response
    {
        /* BaseUser $user */
        $user = $this->getUser();

        if (!$user instanceof CanBeGarageMember) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.not_pro_user_no_garage');
            return $this->redirectToRoute("front_view_current_user_info");
        }
        if (!$user->hasGarage()) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.pro_user_need_garage');
            return $this->redirectToRoute("front_garage_create");
        }

        /* Check request parameters */
        if ($garage == null) {
            if ($vehicle) {
                $garage = $vehicle->getGarage();
            } else {
                throw new BadRequestHttpException('A vehicle to edit or a garage to add a new vehicle, is required');
            }
        } else {
            if ($vehicle && $vehicle->getGarage() != $garage) {
                throw new BadRequestHttpException('A vehicle to edit OR a garage to add a new vehicle, is required, not both');
            }
            if (!$user->isMemberOfGarage($garage)) {
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.vehicle.add_to_garage');
                return $this->redirectToRoute("front_view_current_user_info");
            }
        }

        if ($vehicle) {
            if (!$this->isGranted(ProVehicleVoter::EDIT, $vehicle)) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_DANGER,
                    'flash.error.unauthorized.vehicle.edit'
                );
                return $this->redirectToRoute("front_garage_view", ['slug' => $garage->getSlug()]);
            }
            $vehicleDTO = ProVehicleDTO::buildFromProVehicle($vehicle);
            if (!empty($plateNumber)) {
                $vehicleDTO->getVehicleRegistration()->setPlateNumber($plateNumber);
            }
            $actionRoute = $this->generateUrl('front_vehicle_pro_edit', [
                'vehicle_id' => $vehicle->getId(),
                'plateNumber' => $plateNumber
            ]);
        } else {
            $vehicleDTO = new ProVehicleDTO($plateNumber);
            $vehicleDTO->setCity($garage->getCity());
            $actionRoute = $this->generateUrl('front_vehicle_pro_add', [
                'garage_id' => $garage->getId(),
                'plateNumber' => $plateNumber
            ]);
        }

        $filters = $vehicleDTO->retrieveFilter();

        if ($plateNumber) {
            try {
                $information = $this->autoDataConnector->executeRequest(new GetInformationFromPlateNumber($plateNumber));
                $ktypNumber = null;
                if (isset($information['Vehicule']['ktypnr_aaa']) && !empty($information['Vehicule']['ktypnr_aaa'])) {
                    $ktypNumber = $information['Vehicule']['ktypnr_aaa'];
                } elseif (isset($information['Vehicule']['LTYPVEH'])) {
                    foreach ($information['Vehicule']['LTYPVEH'] as $key => $value) {
                        if (isset($value['KTYPNR']) && !empty($value['KTYPNR'])) {
                            if (!empty($ktypNumber)) {
                                $this->session->getFlashBag()->add(
                                    self::FLASH_LEVEL_DANGER,
                                    'flash.warning.vehicle.multiple_vehicle_types'
                                );
                            }
                            $ktypNumber = $value['KTYPNR'];
                        }
                    }
                };

                if (!empty($ktypNumber)) {
                    $vehicleInfoResultSet = $this->vehicleInfoEntityIndexer->getVehicleInfoByKtypNumber($ktypNumber);
                }
                if (isset($vehicleInfoResultSet) && $vehicleInfoResultSet->getTotalHits() == 1) {
                    $vehicleInfoResult = $vehicleInfoResultSet->getResults()[0];
                    $vehicleInfo = $vehicleInfoResult->getData();

                    if (isset($vehicleInfo['make']) && !empty($vehicleInfo['make'])) {
                        $filters['make'] = $vehicleInfo['make'];
                    } else {
                        $filters['make'] = $information['Vehicule']['MARQUE'];
                    }
                    if (isset($vehicleInfo['model']) && !empty($vehicleInfo['model'])) {
                        $filters['model'] = $vehicleInfo['model'];
                    } else {
                        $filters['model'] = $information['Vehicule']['MODELE_ETUDE'];
                    }
                    if (isset($vehicleInfo['engine']) && !empty($vehicleInfo['engine'])) {
                        $filters['engine'] = $vehicleInfo['engine'];
                    } else {
                        $filters['engine'] = $information['Vehicule']['VERSION'];
                    }

                } else {
                    $filters['make'] = $information['Vehicule']['MARQUE'];
                    $filters['model'] = $information['Vehicule']['MODELE_ETUDE'];
                    $filters['engine'] = $information['Vehicule']['VERSION'];
                }

                $date1erCir = $information['Vehicule']['DATE_1ER_CIR'] ?? null;
                if ($date1erCir) {
                    $vehicleDTO->setRegistrationDate($date1erCir);
                }
                $vin = $information['Vehicule']['CODIF_VIN_PRF'] ?? null;
                if ($vin) {
                    if (strlen($vin) < 17) {
                        $vin = str_pad($vin, 17, '_', STR_PAD_LEFT);
                    }
                    $vehicleDTO->setRegistrationVin($vin);
                }
            } catch (AutodataException $autodataException) {
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_DANGER,
                    $autodataException instanceof AutodataWithUserMessageException ?
                        $autodataException->getMessage() :
                        'flash.warning.registration_recognition_failed'
                );
            }
        }

        $vehicleDTO->updateFromFilters($filters);
        $availableValues = $this->vehicleInfoEntityIndexer->getVehicleInfoAggregatesFromMakeAndModel($filters);
        $proVehicleForm = $this->formFactory->create(
            ProVehicleType::class,
            $vehicleDTO,
            [
                'available_values' => $availableValues,
                'action' => $actionRoute
            ]);
        $proVehicleForm->handleRequest($request);

        if ($proVehicleForm->isSubmitted() && $proVehicleForm->isValid()) {
            if ($vehicle) {
                $this->proVehicleEditionService->updateInformations($vehicleDTO, $vehicle);
                $flashMessage = 'flash.success.vehicle_update';
            } else {
                $vehicle = $this->proVehicleEditionService->createInformations($vehicleDTO, $garage);
                $flashMessage = 'flash.success.vehicle_create';
            }

            $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, $flashMessage);
            return $this->redirSave(['v' => $vehicle->getId(), '_fragment' => 'message-answer-block'], 'front_vehicle_pro_detail', ['slug' => $vehicle->getSlug()]);
        }

        return $this->render('front/Vehicle/Add/add.html.twig', [
            'proVehicleForm' => $proVehicleForm->createView(),
            'vehicle' => $vehicle
        ]);
    }

    /**
     * @Entity("vehicle", expr="repository.findIgnoreSoftDeletedOneBy({'slug':slug})")
     * @param ProVehicle $vehicle
     * @return Response
     */
    public function detailAction(Request $request, ProVehicle $vehicle): Response
    {
        $suggestedSellers = $vehicle->getSuggestedSellers(false, $this->getUser());
        if ($vehicle->getDeletedAt() != null || count($suggestedSellers) == 0) {
            $response = $this->render('front/Exception/error410.html.twig', [
                'titleKey' => 'error_page.vehicle.removed.title',
                'messageKey' => 'error_page.vehicle.removed.body',
                'redirectionUrl' => $this->generateUrl('front_search_by_make_model', [
                    'make' => $vehicle->getMake(),
                    'model' => urlencode($vehicle->getModelName()),
                    'type' => SearchController::QP_TYPE_PRO_VEHICLES
                ])
            ]);
            $response->setStatusCode(Response::HTTP_GONE);
            return $response;
        }

        /** @var BaseUser|ApplicationUser $currentUser */
        $currentUser = $this->getUser();
        $currentUserCanEditThisProVehicle = $this->proVehicleEditionService->canEdit($this->getUser(), $vehicle);

        $userLike = null;
        if ($this->isUserAuthenticated()) {
            $userLike = $vehicle->getLikeOfUser($this->getUser());
        }

        $contactForm = null;
        /* TODO ajouter un sélecteur au formulaire si nécessaire = multivendeur
        if (!$currentUserCanEditThisProVehicle) {

            try {
                $this->conversationAuthorizationChecker->canCommunicate($currentUser, $vehicle->getSeller());

                // $currentUser is logged and can communicate with Wamcar messaging service
                $conversation = $this->conversationRepository->findByUserAndInterlocutor($currentUser, $vehicle->getSeller());
                if ($conversation instanceof Conversation) {
                    // Useless check ?
                    //$this->conversationAuthorizationChecker->memberOfConversation($currentUser, $conversation);
                    $messageDTO = MessageDTO::buildFromConversation($conversation, $currentUser);
                } else {
                    $messageDTO = new MessageDTO(null, $currentUser, $vehicle->getSeller());
                }
                $messageDTO->vehicleHeader = $vehicle;
                $contactForm = $this->formFactory->create(MessageType::class, $messageDTO, ['isContactForm' => true]);
                $contactForm->handleRequest($request);
                if ($contactForm->isSubmitted() && $contactForm->isValid()) {
                    $conversation = $this->conversationEditionService->saveConversation($messageDTO, $conversation);
                    $this->session->getFlashBag()->add(
                        self::FLASH_LEVEL_INFO,
                        'flash.success.conversation_update'
                    );
                    return $this->redirectToRoute('front_conversation_edit', [
                        'id' => $conversation->getId(),
                        '_fragment' => 'last-message']);

                }

            } catch (AccessDeniedHttpException $exception) {
                // $currentUser is unlogged or can't communicate directly => contact form for unlogged user
                $proContactMessageDTO = new ProContactMessageDTO($vehicle->getSeller());
                $contactForm = $this->formFactory->create(ContactProType::class, $proContactMessageDTO);
                $contactForm->handleRequest($request);
                if ($contactForm->isSubmitted() && $contactForm->isValid()) {
                    // Check ReCaptcha validation
                    $captchaVerificationReturn = $this->captchaService->verify(['token' => $request->get($this->captchaService->getClientSidePostParameters())]);
                    if(!$captchaVerificationReturn['success']){
                        $this->session->getFlashBag()->add(
                            self::FLASH_LEVEL_WARNING,
                            'flash.error.captcha_validation'
                        );
                    }else {
                        $proContactMessageDTO->vehicle = $vehicle;
                        $proContactMessage = $this->conversationEditionService->saveProContactMessage($proContactMessageDTO);
                        $this->eventBus->handle(new ProContactMessageCreated($proContactMessage));
                        $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO,
                            $this->translator->trans('flash.success.pro_contact_message.sent', [
                                '%proUserName%' => $vehicle->getSellerName()
                            ]));
                        return $this->redirectToRoute('front_vehicle_pro_detail', ['slug' => $vehicle->getSlug()]);
                    }
                }
            }
        }*/

        return $this->render('front/Vehicle/Detail/detail_proVehicle_peexeo.html.twig', [
            'isEditableByCurrentUser' => $this->proVehicleEditionService->canEdit($this->getUser(), $vehicle),
            'vehicle' => $vehicle,
            'positiveLikes' => $vehicle->getPositiveLikesByUserType(),
            'like' => $userLike,
            'contactForm' => $contactForm ? $contactForm->createView() : null,
            'suggestedSellers' => $suggestedSellers
        ]);
    }

    /**
     * @Entity("vehicle", expr="repository.findIgnoreSoftDeleted(id)")
     * @param ProVehicle $vehicle
     * @return RedirectResponse
     */
    public function legacyDetailAction(ProVehicle $vehicle): Response
    {
        return $this->redirectToRoute('front_vehicle_pro_detail', ['slug' => $vehicle->getSlug()], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @param ProVehicle $proVehicle
     * @return Response
     */
    public function deleteAction(ProVehicle $proVehicle): Response
    {
        if (!$this->isGranted(ProVehicleVoter::EDIT, $proVehicle)) {
            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_DANGER,
                'flash.error.remove_vehicle'
            );
            return $this->redirectToRoute('front_vehicle_pro_detail', [
                'slug' => $proVehicle->getSlug()
            ]);
        }

        $this->proVehicleEditionService->deleteVehicle($proVehicle);

        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_INFO,
            'flash.success.remove_vehicle'
        );

        return $this->redirectToRoute('front_garage_view', [
            'slug' => $proVehicle->getGarage()->getSlug()
        ]);
    }

    /**
     * @param ProVehicle $vehicle
     * @param Request $request
     * @return Response
     */
    public function likeProVehicleAction(ProVehicle $vehicle, Request $request): Response
    {
        if (!$this->isUserAuthenticated()) {
            if ($request->headers->has(self::REQUEST_HEADER_REFERER)) {
                $this->session->set(self::LIKE_REDIRECT_TO_SESSION_KEY, $request->headers->get(self::REQUEST_HEADER_REFERER));
            }
            throw new AccessDeniedException();
        }
        $this->proVehicleEditionService->userLikesVehicle($this->getUser(), $vehicle);

        if ($this->session->has(self::LIKE_REDIRECT_TO_SESSION_KEY) || $request->headers->has(self::REQUEST_HEADER_REFERER)) {
            $referer = $this->session->get(self::LIKE_REDIRECT_TO_SESSION_KEY, $request->headers->get(self::REQUEST_HEADER_REFERER));
            $this->session->remove(self::LIKE_REDIRECT_TO_SESSION_KEY);
            if (!empty($referer)) {
                // Keep the query param from the request
                $queryParam = '';
                if ($request->query->has(SecurityController::INSCRIPTION_QUERY_PARAM)) {
                    $queryParam = str_contains($referer, '?') ? '&' : '?';
                    $queryParam .= SecurityController::INSCRIPTION_QUERY_PARAM . "=" . $request->query->get(SecurityController::INSCRIPTION_QUERY_PARAM);
                }
                if ($referer === $this->generateUrl('front_vehicle_pro_detail', ['slug' => $vehicle->getSlug()])) {
                    return $this->redirect($referer . $queryParam . '#header-' . $vehicle->getId());
                }
                return $this->redirect($referer . $queryParam . '#' . $vehicle->getId());
            }
        }
        return $this->redirectToRoute("front_vehicle_pro_detail", ['slug' => $vehicle->getSlug()]);
    }

    /**
     * @param ProVehicle $vehicle
     * @param Request $request
     * @return Response
     */
    public function ajaxLikeProVehicleAction(ProVehicle $vehicle, Request $request): Response
    {
        if (!$this->isUserAuthenticated()) {
            return new JsonResponse($this->translator->trans('flash.error.user.not_logged'), Response::HTTP_UNAUTHORIZED);
        }
        $this->proVehicleEditionService->userLikesVehicle($this->getUser(), $vehicle);

        return new JsonResponse(count($vehicle->getPositiveLikes()), Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getProVehiclesToDeclareAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $currentUser = $this->getUser();
        if(!$currentUser instanceof ProUser){
            return new JsonResponse($this->translator->trans('flash.error.sale.unauthorized_to_get_vehicle_to_declare'), Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse($this->proVehicleEditionService->getProUserVehiclesForSalesDeclaration($currentUser, $request->query->all()));
    }
}
