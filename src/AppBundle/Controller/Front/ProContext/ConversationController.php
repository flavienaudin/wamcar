<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ApplicationConversation;
use AppBundle\Doctrine\Repository\DoctrineConversationRepository;
use AppBundle\Doctrine\Repository\DoctrineMessageRepository;
use AppBundle\Elasticsearch\Elastica\ElasticUtils;
use AppBundle\Elasticsearch\Elastica\SearchResultProvider;
use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\Type\MessageType;
use AppBundle\Form\Type\SearchVehicleType;
use AppBundle\Services\Conversation\ConversationAuthorizationChecker;
use AppBundle\Services\Conversation\ConversationEditionService;
use AppBundle\Services\Vehicle\VehicleRepositoryResolver;
use AppBundle\Session\Model\SessionMessage;
use AppBundle\Session\SessionMessageManager;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\User\BaseUser;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\BaseVehicle;

class ConversationController extends BaseController
{
    const NB_VEHICLES_PER_PAGE = 10;

    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var ConversationEditionService */
    protected $conversationEditionService;
    /** @var ConversationAuthorizationChecker */
    protected $conversationAuthorizationChecker;
    /** @var DoctrineConversationRepository */
    protected $conversationRepository;
    /** @var VehicleRepositoryResolver */
    protected $vehicleRepositoryResolver;
    /** @var DoctrineMessageRepository */
    protected $messageRepository;
    /** @var SessionMessageManager */
    protected $sessionMessageManager;
    /** @var SearchResultProvider */
    private $searchResultProvider;

    public function __construct(
        FormFactoryInterface $formFactory,
        ConversationEditionService $conversationEditionService,
        ConversationAuthorizationChecker $conversationAuthorizationChecker,
        DoctrineConversationRepository $conversationRepository,
        VehicleRepositoryResolver $vehicleRepositoryResolver,
        DoctrineMessageRepository $messageRepository,
        SessionMessageManager $sessionMessageManager,
        SearchResultProvider $searchResultProvider
    )
    {
        $this->formFactory = $formFactory;
        $this->conversationEditionService = $conversationEditionService;
        $this->conversationAuthorizationChecker = $conversationAuthorizationChecker;
        $this->conversationRepository = $conversationRepository;
        $this->vehicleRepositoryResolver = $vehicleRepositoryResolver;
        $this->messageRepository = $messageRepository;
        $this->sessionMessageManager = $sessionMessageManager;
        $this->searchResultProvider = $searchResultProvider;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $conversations = $this->conversationRepository->findByUser($this->getUser());

        if (count($conversations) > 0) {
            return $this->editAction($request, reset($conversations) ?: null);
        }

        return $this->render('front/Messages/messages_list.html.twig', [
            'user' => $this->getUser(),
            'interlocutor' => null,
            'conversations' => null,
            'currentConversation' => null,
            'messages' => null
        ]);
    }

    /**
     * @param Request $request
     * @param BaseUser $interlocutor
     * @param null|string $vehicleId
     * @return Response
     */
    public function createAction(Request $request, BaseUser $interlocutor, ?string $vehicleId = null): Response
    {
        $this->conversationAuthorizationChecker->canCommunicate($this->getUser(), $interlocutor);
        $messageDTO = new MessageDTO(null, $this->getUser(), $interlocutor);

        //Assign vehicle on message automatically
        if ($this->getUser()->isPersonal()) {
            $userVehicles = $this->getUser()->getVehicles();
            if (count($userVehicles) == 1) {
                $messageDTO->vehicle = $userVehicles->first();
            } elseif (count($userVehicles) > 1) {
                $messageDTO->isFleet = true;
            }
        }

        return $this->processForm($request, $messageDTO, null, $vehicleId);
    }

    /**
     * @param Request $request
     * @param ApplicationConversation $conversation
     * @param null|string $vehicleId
     * @return Response
     */
    public function editAction(Request $request, ApplicationConversation $conversation, ?string $vehicleId = null): Response
    {
        $this->conversationAuthorizationChecker->memberOfConversation($this->getUser(), $conversation);

        $messageDTO = MessageDTO::buildFromConversation($conversation, $this->getUser());
        $this->conversationEditionService->updateLastOpenedAt($conversation, $this->getUser());

        return $this->processForm($request, $messageDTO, $conversation, $vehicleId);
    }

    /**
     * @param Request $request
     * @param MessageDTO $messageDTO
     * @param ApplicationConversation|null $conversation
     * @param null|string $vehicleId
     * @return null|RedirectResponse|Response
     */
    protected function processForm(Request $request, MessageDTO $messageDTO, ?ApplicationConversation $conversation = null, ?string $vehicleId = null)
    {
        $lastVehicleHeaderMessage = $conversation ? $this->messageRepository->getLastVehicleHeader($conversation) : null;
        $vehicleId = $lastVehicleHeaderMessage && $lastVehicleHeaderMessage->getVehicleHeader()->getId() === $vehicleId ? null : $vehicleId;

        $messageDTO = $this->loadAndCleanSession($messageDTO);
        $messageDTO = $this->assignVehicleParams($request, $messageDTO, $vehicleId);

        if (!$conversation) {
            $redirectRoute = $this->redirectIfExistConversation($messageDTO);
            if ($redirectRoute) {
                return $redirectRoute;
            }
        }

        $messageForm = $this->formFactory->create(MessageType::class, $messageDTO, ['user' => $this->getUser()]);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted()) {
            $action = $this->redirectionFromSubmitButton($request, $messageForm);
            if ($action) {
                return $action;
            }

            if ($messageForm->isValid()) {
                $conversation = $this->conversationEditionService->saveConversation($messageDTO, $conversation);
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_INFO,
                    'flash.success.conversation_update'
                );
                return $this->redirectToRoute('front_conversation_edit', [
                    'id' => $conversation->getId(),
                    '_fragment' => 'last-message']);
            }
        }

        $conversations = $this->conversationRepository->findByUser($this->getUser());
        $messages = $conversation ? $this->messageRepository->findByConversationAndOrdered($conversation) : null;

        return $this->render('front/Messages/messages_list.html.twig', [
            'messageForm' => $messageForm->createView(),
            'user' => $this->getUser(),
            'interlocutor' => $messageDTO->interlocutor,
            'vehicleHeader' => $messageDTO->vehicleHeader,
            'conversations' => $conversations,
            'currentConversation' => $conversation,
            'messages' => $messages
        ]);
    }

    /**
     * @param Request $request
     * @param MessageDTO $messageDTO
     * @param string $vehicleId
     * @return MessageDTO
     */
    protected function assignVehicleParams(Request $request, MessageDTO $messageDTO, ?string $vehicleId = null): MessageDTO
    {
        // If vehicle referer
        if ($vehicleId) {
            /** @var BaseVehicle $vehicleHeader */
            $vehicleHeader = $this->vehicleRepositoryResolver->getVehicleRepositoryByUser($messageDTO->interlocutor)->find($vehicleId);
            if ($vehicleHeader === null) {
                $vehicleHeader = $this->vehicleRepositoryResolver->getVehicleRepositoryByUser($messageDTO->user)->find($vehicleId);
            }
            $messageDTO->vehicleHeader = $vehicleHeader;
        }

        //If Vehicle added
        if ($request->query->has('v')) {
            /** @var BaseVehicle $vehicle */
            $vehicle = $this->vehicleRepositoryResolver->getVehicleRepositoryByUser($this->getUser())->find($request->query->get('v'));
            if ($vehicle === null) {
                $vehicle = $this->vehicleRepositoryResolver->getVehicleRepositoryByUser($messageDTO->user)->find($vehicleId);
            }
            if ($vehicle && $vehicle->canEditMe($this->getUser())) {
                $messageDTO->vehicle = $vehicle;
            }
        }

        return $messageDTO;
    }

    /**
     * @param MessageDTO $messageDTO
     * @return MessageDTO
     */
    protected function loadAndCleanSession(MessageDTO $messageDTO): MessageDTO
    {
        $sessionMessageDTO = $this->sessionMessageManager->getMessageDTO();
        $messageDTO->content = $sessionMessageDTO ? $sessionMessageDTO->content : $messageDTO->content;
        $messageDTO->vehicle = $sessionMessageDTO ? $sessionMessageDTO->vehicle : $messageDTO->vehicle;
        $messageDTO->vehicleHeader = $sessionMessageDTO ? $sessionMessageDTO->vehicleHeader : $messageDTO->vehicleHeader;
        $this->sessionMessageManager->clear();

        return $messageDTO;
    }

    /**
     * @param MessageDTO $messageDTO
     * @return null|RedirectResponse
     */
    protected function redirectIfExistConversation(MessageDTO $messageDTO): ?RedirectResponse
    {
        if ($conversation = $this->conversationRepository->findByUserAndInterlocutor($this->getUser(), $messageDTO->interlocutor)) {
            $vehicleId = $messageDTO->vehicleHeader ? $messageDTO->vehicleHeader->getId() : null;
            return $this->redirectToRoute('front_conversation_edit', ['id' => $conversation->getId(), 'vehicleId' => $vehicleId]);
        }

        return null;
    }

    /**
     * @param Request $request
     * @param FormInterface $messageForm
     * @return null|RedirectResponse
     */
    protected function redirectionFromSubmitButton(Request $request, FormInterface $messageForm): ?RedirectResponse
    {
        /** @var MessageDTO $messageDTO */
        $messageDTO = $messageForm->getData();

        switch ($messageForm->getClickedButton()->getName()) {
            case 'selectVehicle':
                $this->sessionMessageManager->set($request->get('_route'), $request->get('_route_params'), $messageDTO);
                return $this->redirectToRoute('front_conversation_vehicle_list');
                break;
            case 'createVehicle':
                $this->sessionMessageManager->set($request->get('_route'), $request->get('_route_params'), $messageDTO);
                $user = $this->getUser();
                if ($user->isPersonal()) {
                    return $this->redirectToRoute('front_vehicle_personal_add');
                } else {
                    /** @var ProUser $user */
                    /** @var Collection $userGarages */
                    $userGarages = $user->getEnabledGarageMemberships();
                    if ($userGarages->isEmpty()) {
                        $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.pro_user_need_garage');
                        return $this->redirectToRoute('front_garage_create');
                    } elseif ($userGarages->count() == 1) {
                        return $this->redirectToRoute('front_vehicle_pro_add', ['garage_id' => $userGarages->first()->getGarage()->getId()]);
                    } else {
                        /* TODO : gérer si le vendeur a plusieurs garages. Action pour l'instant non accessible Cf MessageType */
                        $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.select_garage_first');
                        return $this->redirectToRoute('front_view_current_user_info');
                    }
                }
                break;
        }

        return null;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function vehicleListAction(Request $request): Response
    {
        /** @var SessionMessage $sessionMessage */
        $sessionMessage = $this->sessionMessageManager->get();
        //Redirection to conversation list if no session
        if (!$sessionMessage) {
            return $this->redirectToRoute('front_conversation_list');
        }

        $searchVehicleDTO = new SearchVehicleDTO();
        $searchForm = $this->formFactory->create(SearchVehicleType::class, $searchVehicleDTO, [
            'available_values' => [],
            'small_version' => true
        ]);

        $searchForm->handleRequest($request);

        $page = $request->query->get('page', 1);
        /** @var BaseUser $currentUser */
        $currentUser = $this->getUser();
        $searchResultSet = $this->searchResultProvider->getQueryUserVehiclesResult($currentUser, $searchForm->get("text")->getData(), $page, self::NB_VEHICLES_PER_PAGE);
        $results = array();
        $results['hits'] = array();
        $ids = array();
        if ($searchResultSet != null) {
            $results['totalHits'] = $searchResultSet->getTotalHits();
            foreach ($searchResultSet->getResults() as $result) {
                $userVehicle = $result->getData();
                $ids[] = $userVehicle['id'];
            }
            if (count($ids) > 0) {
                $results['hits'] = $this->vehicleRepositoryResolver->getVehicleRepositoryByUser($currentUser)->findByIds($ids);
            }
            $lastPage = ElasticUtils::numberOfPages($searchResultSet);
        } else {
            $results['totalHits'] = 0;
            $lastPage = 1;
        }

        return $this->render('front/Messages/messages_vehicle_list.html.twig', [
            'vehicles' => $results,
            'linkRoute' => $sessionMessage->route,
            'linkRouteParams' => $sessionMessage->routeParams,
            'page' => $page ?? null,
            'lastPage' => $lastPage ?? null,
            'searchForm' => $searchForm->createView(),
        ]);
    }
}
