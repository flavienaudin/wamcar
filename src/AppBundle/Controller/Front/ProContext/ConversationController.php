<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ApplicationConversation;
use AppBundle\Doctrine\Repository\DoctrineConversationRepository;
use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Form\Type\MessageType;
use AppBundle\Services\Conversation\ConversationAuthorizationChecker;
use AppBundle\Services\Conversation\ConversationEditionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\User\BaseUser;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\Vehicle;

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

    public function __construct(
        FormFactoryInterface $formFactory,
        ConversationEditionService $conversationEditionService,
        ConversationAuthorizationChecker $conversationAuthorizationChecker,
        DoctrineConversationRepository $conversationRepository
    )
    {
        $this->formFactory = $formFactory;
        $this->conversationEditionService = $conversationEditionService;
        $this->conversationAuthorizationChecker = $conversationAuthorizationChecker;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $conversations = $this->conversationRepository->findByUser($this->getUser());


        return $this->render('front/Conversation/list.html.twig', [
            'conversations' => $conversations
        ]);
    }

    /**
     * @ParamConverter("interlocutor", options={"id" = "user_id"})
     * @ParamConverter("vehicle", options={"id" = "vehicle_id"})
     * @param Request $request
     * @param BaseUser $interlocutor
     * @param null|BaseVehicle $vehicle
     * @return Response
     */
    public function createAction(Request $request, BaseUser $interlocutor, BaseVehicle $vehicle): Response
    {
        $this->conversationAuthorizationChecker->canCommunicate($this->getUser(), $interlocutor);

        if ($conversation = $this->conversationEditionService->getConversation($this->getUser(), $interlocutor)) {
            if ($vehicle) {
                return $this->redirectToRoute('front_conversation_edit_vehicle', ['conversation_id' => $conversation->getId(), 'vehicle_id' => $vehicle->getId()]);
            }
            return $this->redirectToRoute('front_conversation_edit', ['conversation_id' => $conversation->getId()]);
        }

        $messageDTO = new MessageDTO(null, $this->getUser(), $interlocutor);

        return $this->processForm($request, $messageDTO);
    }

    /**
     * @ParamConverter("conversation", options={"id" = "conversation_id"})
     * @ParamConverter("vehicle", options={"id" = "vehicle_id"})
     * @param Request $request
     * @param ApplicationConversation $conversation
     * @param null|BaseVehicle $vehicle
     * @return Response
     */
    public function editAction(Request $request, ApplicationConversation $conversation, ?BaseVehicle $vehicle = null): Response
    {
        $messageDTO = MessageDTO::buildFromConversation($conversation, $this->getUser());
        $this->conversationEditionService->updateLastOpenedAt($conversation, $this->getUser());

        return $this->processForm($request, $messageDTO, $conversation);
    }

    /**
     * @param Request $request
     * @param MessageDTO $messageDTO
     * @param null|ApplicationConversation $conversation
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    protected function processForm(Request $request, MessageDTO $messageDTO, ?ApplicationConversation $conversation = null)
    {
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
}
