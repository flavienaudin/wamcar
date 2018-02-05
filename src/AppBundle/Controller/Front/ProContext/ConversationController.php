<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Form\Type\MessageType;
use AppBundle\Services\Conversation\ConversationEditionService;
use AppBundle\Services\User\CanBeInConversation;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Wamcar\User\BaseUser;

class ConversationController extends BaseController
{
    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var ConversationEditionService */
    protected $conversationEditionService;

    public function __construct(
        FormFactoryInterface $formFactory,
        ConversationEditionService $conversationEditionService
    )
    {
        $this->formFactory = $formFactory;
        $this->conversationEditionService = $conversationEditionService;
    }

    /**
     * @param Request $request
     * @param BaseUser $interlocutor
     * @return Response
     */
    public function createAction(Request $request, BaseUser $interlocutor): Response
    {
        if (!$this->authorizationChecker->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException('Only connected user can create conversation');
        }

        if ($this->conversationEditionService->canCommunicate($this->getUser(), $interlocutor)) {
            throw new AccessDeniedHttpException('Not authorized to communicate');
        }

        $messageDTO = new MessageDTO(null, $this->getUser(), $interlocutor);
        $messageForm = $this->formFactory->create(MessageType::class, $messageDTO);

        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $this->conversationEditionService->createConversation($messageDTO);

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                'flash.success.conversation_create'
            );
            return $this->redirectToRoute('front_conversation_create', ['id' => $interlocutor->getId()]);
        }

        return $this->render('front/Conversation/new.html.twig', [
            'messageForm' => $messageForm->createView(),
            'user' => $this->getUser(),
            'interlocutor' => $interlocutor
        ]);
    }

}
