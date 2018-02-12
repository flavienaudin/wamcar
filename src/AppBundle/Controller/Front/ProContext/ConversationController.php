<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ApplicationConversation;
use AppBundle\Doctrine\Repository\DoctrineConversationRepository;
use AppBundle\Doctrine\Repository\DoctrineMessageRepository;
use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Form\Type\MessageType;
use AppBundle\Services\Conversation\ConversationAuthorizationChecker;
use AppBundle\Services\Conversation\ConversationEditionService;
use AppBundle\Services\Vehicle\VehicleRepositoryResolver;
use AppBundle\Session\ConversationSession;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\User\BaseUser;
use Wamcar\Vehicle\BaseVehicle;

class ConversationController extends BaseController
{
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
    /** @var ConversationSession */
    protected $conversationSession;

    public function __construct(
        FormFactoryInterface $formFactory,
        ConversationEditionService $conversationEditionService,
        ConversationAuthorizationChecker $conversationAuthorizationChecker,
        DoctrineConversationRepository $conversationRepository,
        VehicleRepositoryResolver $vehicleRepositoryResolver,
        DoctrineMessageRepository $messageRepository,
        ConversationSession $conversationSession
    )
    {
        $this->formFactory = $formFactory;
        $this->conversationEditionService = $conversationEditionService;
        $this->conversationAuthorizationChecker = $conversationAuthorizationChecker;
        $this->conversationRepository = $conversationRepository;
        $this->vehicleRepositoryResolver = $vehicleRepositoryResolver;
        $this->messageRepository = $messageRepository;
        $this->conversationSession = $conversationSession;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $conversations = $this->conversationRepository->findByUser($this->getUser());

        if (count($conversations)> 0 ) {
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
        if ($vehicleId) {
            /** @var BaseVehicle $vehicleHeader */
            $vehicleHeader = $this->vehicleRepositoryResolver->getVehicleRepositoryByUser($messageDTO->interlocutor)->find($vehicleId);
            $messageDTO->vehicleHeader =$vehicleHeader;
        }

        if (!$conversation) {
            $redirectRoute = $this->redirectIfExistConversation($messageDTO);
            if ($redirectRoute) {
                return $redirectRoute;
            }
        }

        $messageForm = $this->formFactory->create(MessageType::class, $messageDTO);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted()) {
            $action = $this->redirectionFormSubmit($request, $messageForm);
            if ($action) {
                return $action;
            }

            if ($messageForm->isValid()) {
                $conversation = $this->conversationEditionService->saveConversation($messageDTO, $conversation);
                $this->session->getFlashBag()->add(
                    self::FLASH_LEVEL_INFO,
                    'flash.success.conversation_update'
                );
                return $this->redirectToRoute('front_conversation_edit', ['id' => $conversation->getId()]);
            }
        }

        $conversations = $this->conversationRepository->findByUser($this->getUser());
        $messages = $conversation ? $this->messageRepository->findByConversationAndOrdered($conversation) : null;

        return $this->render('front/Messages/messages_list.html.twig', [
            'messageForm' => $messageForm->createView(),
            'user' => $this->getUser(),
            'interlocutor' => $messageDTO->interlocutor,
            'conversations' => $conversations,
            'currentConversation' => $conversation,
            'messages' => $messages
        ]);
    }

    /**
     * @param MessageDTO $messageDTO
     * @return null|RedirectResponse
     */
    protected function redirectIfExistConversation(MessageDTO $messageDTO): ?RedirectResponse
    {
        if ($conversation = $this->conversationRepository->findByUserAndInterlocutor($this->getUser(), $messageDTO->interlocutor)) {
            $vehicleId = $messageDTO->vehicleHeader? $messageDTO->vehicleHeader->getId() : null;
            return $this->redirectToRoute('front_conversation_edit', ['id' => $conversation->getId(), 'vehicleId' => $vehicleId]);
        }

        return null;
    }

    protected function redirectionFormSubmit(Request $request, FormInterface $messageForm)
    {
        switch ($messageForm->getClickedButton()->getName()) {
            case 'selectVehicle':
                $this->conversationSession->saveMessageDTOInSession($request->get('_route'), $request->get('_route_params'), $messageForm->getData());
                return $this->redirectToRoute('front_conversation_vehicle_list');
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
        return $this->render('front/Messages/messages_vehicle_list.html.twig', [
            'vehicles' => $this->getUser()->getVehicles()
        ]);
    }
}
