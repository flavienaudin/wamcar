<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ApplicationConversation;
use AppBundle\Doctrine\Repository\DoctrineConversationRepository;
use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Form\Type\MessageType;
use AppBundle\Services\Conversation\ConversationAuthorizationChecker;
use AppBundle\Services\Conversation\ConversationEditionService;
use AppBundle\Services\Vehicle\VehicleRepositoryResolver;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\User\BaseUser;

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

    public function __construct(
        FormFactoryInterface $formFactory,
        ConversationEditionService $conversationEditionService,
        ConversationAuthorizationChecker $conversationAuthorizationChecker,
        DoctrineConversationRepository $conversationRepository,
        VehicleRepositoryResolver $vehicleRepositoryResolver
    )
    {
        $this->formFactory = $formFactory;
        $this->conversationEditionService = $conversationEditionService;
        $this->conversationAuthorizationChecker = $conversationAuthorizationChecker;
        $this->conversationRepository = $conversationRepository;
        $this->vehicleRepositoryResolver = $vehicleRepositoryResolver;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $conversations = $this->conversationRepository->findByUser($this->getUser());


        return $this->render('front/Messages/messages_list.html.twig', [
            'conversations' => $conversations
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
     * @return RedirectResponse|Response
     */
    protected function processForm(Request $request, MessageDTO $messageDTO, ?ApplicationConversation $conversation = null, ?string $vehicleId = null)
    {
        if ($vehicleId) {
            $vehicleHeader = $this->vehicleRepositoryResolver->getVehicleRepositoryByUser($messageDTO->interlocutor)->find($vehicleId);
            $messageDTO->vehicleHeaderId = ($vehicleHeader ? $vehicleHeader->getId() : null);
        }

        if (!$conversation) {
            $redirectRoute = $this->redirectIfExistConversation($messageDTO);
            if ($redirectRoute) {
                return $redirectRoute;
            }
        }

        $messageForm = $this->formFactory->create(MessageType::class, $messageDTO);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $conversation = $this->conversationEditionService->saveConversation($messageDTO, $conversation);

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.conversation_update'
            );
            return $this->redirectToRoute('front_conversation_edit', ['id' => $conversation->getId()]);
        }

        return $this->render('front/Conversation/detail.html.twig', [
            'messageForm' => $messageForm->createView(),
            'user' => $this->getUser(),
            'interlocutor' => $messageDTO->interlocutor
        ]);
    }

    /**
     * @param MessageDTO $messageDTO
     * @return null|RedirectResponse
     */
    protected function redirectIfExistConversation(MessageDTO $messageDTO): ?RedirectResponse
    {
        if ($conversation = $this->conversationRepository->findByUserAndInterlocutor($this->getUser(), $messageDTO->interlocutor)) {
            return $this->redirectToRoute('front_conversation_edit', ['id' => $conversation->getId(), 'vehicleId' => $messageDTO->vehicleHeaderId]);
        }

        return null;
    }
}
