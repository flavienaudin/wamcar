<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ApplicationConversation;
use AppBundle\Doctrine\Repository\DoctrineConversationRepository;
use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Form\Type\MessageType;
use AppBundle\Services\Conversation\ConversationAuthorizationChecker;
use AppBundle\Services\Conversation\ConversationEditionService;
use Symfony\Component\Form\FormFactoryInterface;
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
     * @param Request $request
     * @param BaseUser $interlocutor
     * @return Response
     */
    public function createAction(Request $request, BaseUser $interlocutor): Response
    {
        $this->conversationAuthorizationChecker->canCommunicate($this->getUser(), $interlocutor);

        if ($conversation = $this->conversationEditionService->getConversation($this->getUser(), $interlocutor)) {
            return $this->redirectToRoute('front_conversation_edit', ['id' => $conversation->getId()]);
        }

        $messageDTO = new MessageDTO(null, $this->getUser(), $interlocutor);

        return $this->processForm($request, $messageDTO);
    }

    /**
     * @param Request $request
     * @param ApplicationConversation $conversation
     * @return Response
     */
    public function editAction(Request $request, ApplicationConversation $conversation): Response
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
