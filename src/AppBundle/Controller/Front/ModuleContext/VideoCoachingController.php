<?php


namespace AppBundle\Controller\Front\ModuleContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\ScriptSequenceDTO;
use AppBundle\Form\DTO\ScriptVersionDTO;
use AppBundle\Form\DTO\VideoProjectDocumentDTO;
use AppBundle\Form\DTO\VideoProjectDTO;
use AppBundle\Form\DTO\VideoProjectMessageDTO;
use AppBundle\Form\DTO\VideoProjectViewersDTO;
use AppBundle\Form\DTO\VideoVersionDTO;
use AppBundle\Form\Type\ScriptSequenceType;
use AppBundle\Form\Type\ScriptVersionTitleType;
use AppBundle\Form\Type\ScriptVersionType;
use AppBundle\Form\Type\VideoProjectBannerType;
use AppBundle\Form\Type\VideoProjectCoworkersSelectionType;
use AppBundle\Form\Type\VideoProjectDocumentType;
use AppBundle\Form\Type\VideoProjectFollowersByEmailType;
use AppBundle\Form\Type\VideoProjectMessageType;
use AppBundle\Form\Type\VideoProjectType;
use AppBundle\Form\Type\VideoVersionType;
use AppBundle\Security\Voter\VideoCoachingVoter;
use AppBundle\Services\VideoCoaching\VideoProjectScriptService;
use AppBundle\Services\VideoCoaching\VideoProjectService;
use AppBundle\Services\VideoCoaching\VideoVersionService;
use Psr\Http\Message\StreamInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\User\ProUser;
use Wamcar\VideoCoaching\ScriptSequence;
use Wamcar\VideoCoaching\ScriptVersion;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectDocument;
use Wamcar\VideoCoaching\VideoProjectIteration;
use Wamcar\VideoCoaching\VideoVersion;

class VideoCoachingController extends BaseController
{
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var TranslatorInterface $translator */
    private $translator;
    /** @var VideoProjectService */
    private $videoProjectService;
    /** @var VideoProjectScriptService */
    private $videoProjectScriptService;
    /** @var VideoVersionService */
    private $videoVersionService;


    /**
     * VideoCoachingController constructor.
     * @param FormFactoryInterface $formFactory
     * @param TranslatorInterface $translator
     * @param VideoProjectService $videoProjectService
     * @param VideoProjectScriptService $videoProjectScriptService
     * @param VideoVersionService $videoVersionService
     */
    public function __construct(FormFactoryInterface $formFactory,
                                TranslatorInterface $translator,
                                VideoProjectService $videoProjectService,
                                VideoProjectScriptService $videoProjectScriptService,
                                VideoVersionService $videoVersionService)
    {
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->videoProjectService = $videoProjectService;
        $this->videoProjectScriptService = $videoProjectScriptService;
        $this->videoVersionService = $videoVersionService;
    }

    /**
     * @param Request $request
     * @ParamConverter("videoProject", class="Wamcar\VideoCoaching\VideoProject", options={"id"="videoProjectId"})
     * @ParamConverter("videoProjectIteration", class="Wamcar\VideoCoaching\VideoProjectIteration", options={"id" = "iterationId"})
     * @param VideoProject $videoProject
     * @param VideoProjectIteration|null $videoProjectIteration
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function viewAction(Request $request, VideoProject $videoProject, VideoProjectIteration $videoProjectIteration = null)
    {
        /** @var ProUser $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted(VideoCoachingVoter::MODULE_ACCESS, $currentUser)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.module_access');
            return $this->redirectToRoute('front_view_current_user_info');
        }

        if (!$this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_VIEW, $videoProject)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.video_project.view');
            return $this->redirectToRoute('front_view_current_user_info');
        }

        // Set current video project iteration if defined by the route param "iterationId"
        if ($videoProjectIteration != null && $videoProjectIteration->getVideoProject() !== $videoProject) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.video_project.view');
            return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                'videoProjectId' => $videoProject->getId()
            ]);
        } else if ($videoProjectIteration == null) {
            $videoProjectIteration = $videoProject->getLastIteration();
        }

        $editVideoProjectForm = null;
        $editVideoProjectBannerForm = null;
        $selectCoworkersVideoProjectForm = null;
        $addFollowersByEmailVideoProjectForm = null;
        // Formulaire d'édition du projet vidéo
        if ($this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_EDIT, $videoProject)) {
            $videoProjectDTO = VideoProjectDTO::buildFromVideoProject($videoProject);

            // Formulaire d'édition des informations du projet video (title, description)
            $editVideoProjectForm = $this->formFactory->create(VideoProjectType::class, $videoProjectDTO);
            $editVideoProjectForm->handleRequest($request);
            if ($editVideoProjectForm->isSubmitted() && $editVideoProjectForm->isValid()) {
                $videoProject = $this->videoProjectService->update($videoProjectDTO, $videoProject);
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.videoproject.save');
                return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                    'videoProjectId' => $videoProject->getId(),
                    'iterationId' => $videoProjectIteration->getId()
                ]);
            }

            // Formulaire d'édition de l'image de couverture du projet video (banner)
            $editVideoProjectBannerForm = $this->formFactory->create(VideoProjectBannerType::class, $videoProjectDTO);
            $editVideoProjectBannerForm->handleRequest($request);
            if ($editVideoProjectBannerForm->isSubmitted() && $editVideoProjectBannerForm->isValid()) {
                $videoProject = $this->videoProjectService->updateBanner($videoProjectDTO, $videoProject);
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.videoproject.banner.edit');
                return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                    'videoProjectId' => $videoProject->getId(),
                    'iterationId' => $videoProjectIteration->getId()
                ]);
            }

            // Formulaire de partage du projet vidéo avec les membres des entreprises
            $coworkersDTO = new VideoProjectViewersDTO($videoProject);
            $selectCoworkersVideoProjectForm = $this->formFactory->create(VideoProjectCoworkersSelectionType::class, $coworkersDTO, ['coworkers' => $currentUser->getCoworkers()]);
            $selectCoworkersVideoProjectForm->handleRequest($request);
            if ($selectCoworkersVideoProjectForm->isSubmitted() && $selectCoworkersVideoProjectForm->isValid()) {
                $this->videoProjectService->updateVideoProjectCoworkers($videoProject, $coworkersDTO->getCoworkers());
                return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                    'videoProjectId' => $videoProject->getId(),
                    'iterationId' => $videoProjectIteration->getId()
                ]);
            }

            // Formulaire de partage du projet vidéo avec d'autres utilisateurs via e-mail
            $addFollowersByEmailVideoProjectForm = $this->formFactory->create(VideoProjectFollowersByEmailType::class);
            $addFollowersByEmailVideoProjectForm->handleRequest($request);
            if ($addFollowersByEmailVideoProjectForm->isSubmitted() && $addFollowersByEmailVideoProjectForm->isValid()) {
                $results = $this->videoProjectService->shareVideoProjectToUsersByEmails($videoProject, $addFollowersByEmailVideoProjectForm->get('emails')->getData());
                if (count($results[VideoProjectService::VIDEOCOACHING_SHARE_VIDEOPROJECT_FAIL]) > 0) {
                    $emailsNotFoundList = join(', ', array_values($results[VideoProjectService::VIDEOCOACHING_SHARE_VIDEOPROJECT_FAIL]));
                    $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING,
                        $this->translator->transChoice('flash.warning.video_project.share.not_found',
                            count($results[VideoProjectService::VIDEOCOACHING_SHARE_VIDEOPROJECT_FAIL]),
                            ['%prousers_notfound_list%' => $emailsNotFoundList]
                        ));
                }
                return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                    'videoProjectId' => $videoProject->getId(),
                    'iterationId' => $videoProjectIteration->getId()
                ]);
            }
        }

        // Formulaire d'ajout d'une version de script:
        $createScriptVersionForm = null;
        if ($this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_ITERATION_ADD_SCRIPTVERSION, $videoProjectIteration)) {
            $scriptVersionDTO = new ScriptVersionDTO($videoProjectIteration);
            $createScriptVersionForm = $this->formFactory->createNamed('addScriptVersion', ScriptVersionTitleType::class, $scriptVersionDTO);
            $createScriptVersionForm->handleRequest($request);
            if ($createScriptVersionForm->isSubmitted() && $createScriptVersionForm->isValid()) {
                $scriptVersion = $this->videoProjectScriptService->create($scriptVersionDTO);
                // $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.scriptversion.save');
                return $this->redirectToRoute('front_coachingvideo_scriptversion_wizard', [
                    'id' => $scriptVersion->getId()
                ]);
            }
        }

        // Formulaire d'édition des versions de la vidéo
        $editScriptVersionTitleForms = [];
        /** @var ScriptVersion $scriptVersion */
        foreach ($videoProjectIteration->getScriptVersions() as $scriptVersion) {
            if ($this->isGranted(VideoCoachingVoter::SCRIPT_VERSION_EDIT, $scriptVersion)) {
                $editScriptVersionDTO = ScriptVersionDTO::buildFromScriptVersion($scriptVersion);
                $editScriptVersionTitleForm = $this->formFactory->createNamed('editScriptVersionTitle' . $scriptVersion->getId(), ScriptVersionTitleType::class, $editScriptVersionDTO);
                $editScriptVersionTitleForm->handleRequest($request);
                if ($editScriptVersionTitleForm->isSubmitted() && $editScriptVersionTitleForm->isValid()) {
                    $this->videoProjectScriptService->updateMainInfo($editScriptVersionDTO, $scriptVersion);
                    $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.scriptversion.update');
                    return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                        'videoProjectId' => $videoProject->getId(),
                        'iterationId' => $videoProjectIteration->getId()
                    ]);
                }
                $editScriptVersionTitleForms[$scriptVersion->getId()] = $editScriptVersionTitleForm->createView();
            }
        }

        // Formulaire d'ajout d'une version de vidéo :
        $createVideoVersionForm = null;
        if ($this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_ITERATION_ADD_VIDEOVERSION, $videoProjectIteration)) {
            $videoVersionDTO = new VideoVersionDTO($videoProjectIteration);
            $createVideoVersionForm = $this->formFactory->createNamed('addVideoVersion', VideoVersionType::class, $videoVersionDTO);
            $createVideoVersionForm->handleRequest($request);
            if ($createVideoVersionForm->isSubmitted() && $createVideoVersionForm->isValid()) {
                $this->videoVersionService->create($videoVersionDTO);
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.videoversion.save');
                return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                    'videoProjectId' => $videoProject->getId(),
                    'iterationId' => $videoProjectIteration->getId()
                ]);
            }
        }

        // Formulaire d'édition des versions de la vidéo
        $editVideoVersionFormViews = [];
        /** @var VideoVersion $videoVersion */
        foreach ($videoProjectIteration->getVideoVersions() as $videoVersion) {
            if ($this->isGranted(VideoCoachingVoter::VIDEO_VERSION_EDIT, $videoVersion)) {
                $videoVersionDTO = VideoVersionDTO::buildFromVideoVersion($videoVersion);
                $editScriptVersionTitleForm = $this->formFactory->createNamed('editVideoVersion' . $videoVersion->getId(), VideoVersionType::class, $videoVersionDTO);
                $editScriptVersionTitleForm->handleRequest($request);
                if ($editScriptVersionTitleForm->isSubmitted() && $editScriptVersionTitleForm->isValid()) {
                    $this->videoVersionService->update($videoVersionDTO, $videoVersion);
                    $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.videoversion.update');
                    return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                        'videoProjectId' => $videoProject->getId(),
                        'iterationId' => $videoProjectIteration->getId()
                    ]);
                }
                $editVideoVersionFormViews[$videoVersion->getId()] = $editScriptVersionTitleForm->createView();
            }
        }

        // Project Files Library
        $addDocumentForm = null;
        if ($this->isGranted(VideoCoachingVoter::LIBRARY_ADD_DOCUMENT, $videoProject)) {
            $this->videoProjectService->initializeGoogleStorageBucket($videoProject);

            $addVideoProjectDocumentDTO = new VideoProjectDocumentDTO($videoProject);
            $addDocumentForm = $this->formFactory->createNamed('addVideoProjectDocument', VideoProjectDocumentType::class, $addVideoProjectDocumentDTO);
            $addDocumentForm->handleRequest($request);
            if ($addDocumentForm->isSubmitted() && $addDocumentForm->isValid()) {
                $this->videoProjectService->addDocument($addVideoProjectDocumentDTO);
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.videoproject.document.add');
                return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                    'videoProjectId' => $videoProject->getId(),
                    'iterationId' => $videoProjectIteration->getId()
                ]);
            }
        }

        // Form submission handle in dedicated action for ajax management
        $messageForm = $this->formFactory->create(VideoProjectMessageType::class, new VideoProjectMessageDTO($videoProject, $currentUser));

        return $this->render('front/VideoCoaching/VideoProject/view.html.twig', [
            'videoProject' => $videoProject,
            'videoProjectIteration' => $videoProjectIteration,
            'editVideoProjectForm' => $editVideoProjectForm ? $editVideoProjectForm->createView() : null,
            'editVideoProjectBannerForm' => $editVideoProjectBannerForm ? $editVideoProjectBannerForm->createView() : null,
            'selectCoworkersVideoProjectForm' => $selectCoworkersVideoProjectForm ? $selectCoworkersVideoProjectForm->createView() : null,
            'addFollowersByEmailVideoProjectForm' => $addFollowersByEmailVideoProjectForm ? $addFollowersByEmailVideoProjectForm->createView() : null,
            'createScriptVersionForm' => $createScriptVersionForm ? $createScriptVersionForm->createView() : null,
            'editScriptVersionTitleForms' => $editScriptVersionTitleForms,
            'createVideoVersionForm' => $createVideoVersionForm ? $createVideoVersionForm->createView() : null,
            'editVideoVersionForms' => $editVideoVersionFormViews,
            'discussionMessageForm' => $messageForm ? $messageForm->createView() : null,
            'addDocumentForm' => $addDocumentForm ? $addDocumentForm->createView() : null
        ]);
    }

    /**
     * @param VideoProject $videoProject
     * @return RedirectResponse
     */
    public function deleteAction(VideoProject $videoProject)
    {
        /** @var ProUser $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted(VideoCoachingVoter::MODULE_ACCESS, $currentUser)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.module_access');
            return $this->redirectToRoute('front_view_current_user_info');
        }

        if (!$this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_DELETE, $videoProject)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.video_project.delete');
            return $this->redirectToRoute('front_view_current_user_info');
        }
        $this->videoProjectService->delete($videoProject);
        $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.videoproject.delete');
        return $this->redirectToRoute('front_view_current_user_info');
    }

    /**
     * @param ScriptVersion $scriptVersion
     * @return Response
     * @throws \Exception
     */
    public function scriptVersionWizardAction(Request $request, ScriptVersion $scriptVersion)
    {
        /** @var ProUser $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted(VideoCoachingVoter::MODULE_ACCESS, $currentUser)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.module_access');
            return $this->redirectToRoute('front_view_current_user_info');
        }

        $videoProjectIteration = $scriptVersion->getVideoProjectIteration();
        $videoProject = $videoProjectIteration->getVideoProject();
        if (!$this->isGranted(VideoCoachingVoter::SCRIPT_VERSION_EDIT, $scriptVersion)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.script_version.edit');
            return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                'videoProjectId' => $videoProject->getId(),
                'iterationId' => $videoProjectIteration->getId()
            ]);
        }
        $scriptVersionDTO = ScriptVersionDTO::buildFromScriptVersion($scriptVersion);
        $scriptVersionForm = $this->formFactory->create(ScriptVersionType::class, $scriptVersionDTO);
        $scriptVersionForm->handleRequest($request);
        if ($scriptVersionForm->isSubmitted() && $scriptVersionForm->isValid()) {
            $this->videoProjectScriptService->updateScriptSections($scriptVersionDTO, $scriptVersion);

            $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.scriptversion.save');
            return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                'videoProjectId' => $videoProject->getId(),
                'iterationId' => $videoProjectIteration->getId()
            ]);
        }
        return $this->render("front/VideoCoaching/ScriptVersion/wizard_add_script_version.html.twig", [
            'scriptVersion' => $scriptVersion,
            'scriptVersionForm' => $scriptVersionForm->createView()
        ]);
    }

    /**
     * @param ScriptVersion $scriptVersion
     * @return RedirectResponse
     */
    public function deleteScriptVersionAction(ScriptVersion $scriptVersion)
    {
        /** @var ProUser $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted(VideoCoachingVoter::MODULE_ACCESS, $currentUser)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.module_access');
            return $this->redirectToRoute('front_view_current_user_info');
        }

        $videoProject = $scriptVersion->getVideoProjectIteration()->getVideoProject();
        if (!$this->isGranted(VideoCoachingVoter::SCRIPT_VERSION_DELETE, $scriptVersion)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.script_version.delete');
            return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                'videoProjectId' => $videoProject->getId(),
                'iterationId' => $scriptVersion->getVideoProjectIteration()->getId()
            ]);
        }

        $this->videoProjectScriptService->delete($scriptVersion);
        $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.scriptversion.delete');
        return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
            'videoProjectId' => $videoProject->getId(),
            'iterationId' => $scriptVersion->getVideoProjectIteration()->getId()
        ]);
    }

    /**
     * @param VideoVersion $videoVersion
     * @return RedirectResponse
     */
    public function deleteVideoVersionAction(VideoVersion $videoVersion)
    {
        /** @var ProUser $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted(VideoCoachingVoter::MODULE_ACCESS, $currentUser)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.module_access');
            return $this->redirectToRoute('front_view_current_user_info');
        }

        $videoProject = $videoVersion->getVideoProjectIteration()->getVideoProject();
        if (!$this->isGranted(VideoCoachingVoter::VIDEO_VERSION_DELETE, $videoVersion)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.video_version.delete');
            return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                'videoProjectId' => $videoProject->getId(),
                'iterationId' => $videoVersion->getVideoProjectIteration()->getId()
            ]);
        }

        $this->videoVersionService->delete($videoVersion);
        $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.videoversion.delete');
        return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
            'videoProjectId' => $videoProject->getId(),
            'iterationId' => $videoVersion->getVideoProjectIteration()->getId()
        ]);
    }

    /**
     * Retourne le formulaire d'édition de la séquence, dans une modale et traite la soumissions du formulaire le cas échéant
     * @param ScriptSequence $scriptSequence
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function editScriptSequenceAjaxAction(ScriptSequence $scriptSequence, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $videoScriptVersion = $scriptSequence->getScriptSection()->getScriptVersion();
        $videoProject = $videoScriptVersion->getVideoProjectIteration()->getVideoProject();
        if (!$this->isGranted(VideoCoachingVoter::SCRIPT_VERSION_EDIT, $videoScriptVersion)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.script_version.edit');
            return $this->redirectToRoute('front_view_current_user_info');
        }

        $scriptSequenceDTO = ScriptSequenceDTO::buildFromScriptSequence($scriptSequence);
        $scriptSequenceEditForm = $this->formFactory->create(ScriptSequenceType::class, $scriptSequenceDTO);
        $scriptSequenceEditForm->handleRequest($request);
        if ($scriptSequenceEditForm->isSubmitted()) {
            if ($scriptSequenceEditForm->isValid()) {

                $this->videoProjectScriptService->updateScriptsequence($scriptSequenceDTO, $scriptSequence);

                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.scriptsequence.save');
                return new JsonResponse([
                    'redirectTo' => $this->generateUrl('front_coachingvideo_videoproject_view', [
                        'videoProjectId' => $videoProject->getId()
                    ])]);
            } else {
                return new JsonResponse([
                    'html' => $this->renderTemplate(':front/VideoCoaching/ScriptSequence/includes:modal_script_sequence_form.html.twig', [
                        'scriptSequenceEditForm' => $scriptSequenceEditForm->createView()
                    ])], Response::HTTP_BAD_REQUEST);
            }
        }
        $modalId = 'js-edit-scriptsequence-' . $scriptSequence->getId();
        return new JsonResponse([
            'modalId' => $modalId,
            'html' => $this->renderTemplate(':front/VideoCoaching/ScriptSequence/includes:modal_script_sequence_form.html.twig', [
                'modalId' => $modalId,
                'scriptSequence' => $scriptSequence,
                'scriptSequenceEditForm' => $scriptSequenceEditForm->createView()
            ])
        ]);
    }

    /**
     * Ajax request to post a message of video project
     * @param VideoProject $videoProject
     * @param Request $request
     * @return JsonResponse
     */
    public function postVideoProjectMessageAction(VideoProject $videoProject, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        /** @var ProUser $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted(VideoCoachingVoter::MODULE_ACCESS, $currentUser)) {
            return new JsonResponse($this->translator->trans('flash.error.unauthorized.video_coaching.module_access'), Response::HTTP_FORBIDDEN);
        }
        if (!$this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_VIEW, $videoProject)) {
            return new JsonResponse($this->translator->trans('flash.error.unauthorized.video_coaching.video_project.view'), Response::HTTP_FORBIDDEN);
        }

        $messageDTO = new VideoProjectMessageDTO($videoProject, $currentUser);
        $messageForm = $this->formFactory->create(VideoProjectMessageType::class, $messageDTO);
        $messageForm->handleRequest($request);
        if ($messageForm->isSubmitted()) {
            if ($messageForm->isValid()) {
                $this->videoProjectService->addMessage($messageDTO);

                $messageDTO = new VideoProjectMessageDTO($videoProject, $currentUser);
                $messageForm = $this->formFactory->create(VideoProjectMessageType::class, $messageDTO);
            }
            return new JsonResponse([
                'messageForm' => $this->renderTemplate(
                    'front/VideoCoaching/VideoProject/Messages/includes/form.html.twig', [
                        'videoProject' => $videoProject,
                        'messageForm' => $messageForm ? $messageForm->createView() : null
                    ]
                )
            ]);
        }
        return new JsonResponse(['error' => 'no submitted form'], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Ajax request to get last messages of video project
     * @param VideoProject $videoProject
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function getMessagesAction(VideoProject $videoProject, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        /** @var ProUser $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted(VideoCoachingVoter::MODULE_ACCESS, $currentUser)) {
            return new JsonResponse($this->translator->trans('flash.error.unauthorized.video_coaching.module_access'), Response::HTTP_FORBIDDEN);
        }
        if (!$this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_VIEW, $videoProject)) {
            return new JsonResponse($this->translator->trans('flash.error.unauthorized.video_coaching.video_project.view'), Response::HTTP_FORBIDDEN);
        }

        $start = null;
        $startParam = $request->get('start', null);
        if ($startParam) {
            $start = new \DateTime();
            $start->setTimestamp(intval($startParam));
        }

        $end = null;
        $endParam = $request->get('end', null);
        if ($endParam) {
            $end = new \DateTime();
            $end->setTimestamp(intval($endParam));
        }

        $showPreviousParam = boolval($request->get('showPrevious', false));

        $messages = $this->videoProjectService->getMessages($videoProject, $start, $end);
        return new JsonResponse([
            "start" => $start ? $start->getTimestamp() : null,
            "end" => $end ? $end->getTimestamp() : null,
            "firstMessageDate" => isset($messages[0]) ? $messages[0]->getCreatedAt()->getTimestamp() : null,
            "lastMessageDate" => isset($messages[count($messages) - 1]) ? $messages[count($messages) - 1]->getCreatedAt()->getTimestamp() : null,
            "messages" => $this->renderTemplate('front/VideoCoaching/VideoProject/Messages/includes/view.html.twig', [
                "messages" => $messages,
                "videoProjectViewer" => $videoProject->getViewerInfo($currentUser),
                "start" => $start ? $start->getTimestamp() : null,
                "end" => $end ? $end->getTimestamp() : null,
                "showPrevious" => $showPreviousParam
            ])
        ]);
    }


    /**
     * @param Request $request
     * @param VideoProject $videoProject
     * @return JsonResponse
     * @throws \Exception
     */
    public function visiteDiscussionAction(Request $request, VideoProject $videoProject): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        /** @var ProUser $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted(VideoCoachingVoter::MODULE_ACCESS, $currentUser)) {
            return new JsonResponse($this->translator->trans('flash.error.unauthorized.video_coaching.module_access'), Response::HTTP_FORBIDDEN);
        }
        if (!$this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_VIEW, $videoProject)) {
            return new JsonResponse($this->translator->trans('flash.error.unauthorized.video_coaching.video_project.view'), Response::HTTP_FORBIDDEN);
        }

        if ($this->videoProjectService->updateVisitedAt($videoProject, $currentUser)) {
            return new JsonResponse('Ok');
        } else {
            return new JsonResponse('unauthorized', Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * @param VideoProjectDocument $videoProjectDocument
     * @return RedirectResponse|StreamedResponse
     */
    public function getDocumentAction(VideoProjectDocument $videoProjectDocument)
    {
        /** @var ProUser $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted(VideoCoachingVoter::MODULE_ACCESS, $currentUser)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.module_access');
            return $this->redirectToRoute('front_view_current_user_info');
        }

        if (!$this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_VIEW, $videoProjectDocument->getVideoProject())) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.video_project.view');
            return $this->redirectToRoute('front_view_current_user_info');
        }

        /** @var StreamInterface $documentAsStream */
        $documentAsStream = $this->videoProjectService->getFileStreamOfDocument($videoProjectDocument);

        $response = new StreamedResponse(function () use ($documentAsStream) {
            $documentAsStream->rewind();
            while (!$documentAsStream->eof()) {
                echo $documentAsStream->read(8192);
            }
        });

        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $videoProjectDocument->getFileOriginalName());
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @param Request $request
     * @param VideoProjectDocument $videoProjectDocument
     * @return JsonResponse
     */
    public function deleteDocumentAction(Request $request, VideoProjectDocument $videoProjectDocument)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }
        if (!$this->isGranted(VideoCoachingVoter::LIBRARY_DELETE_DOCUMENT, $videoProjectDocument)) {
            return new JsonResponse(['message' => $this->translator->trans('flash.error.videoproject.document.delete.unauthorized')], Response::HTTP_UNAUTHORIZED);
        }

        if ($this->videoProjectService->deleteDocument($videoProjectDocument)) {
            return new JsonResponse(['message' => $this->translator->trans('flash.success.videoproject.document.delete')]);
        } else {
            return new JsonResponse(['errorMessage' => $this->translator->trans('flash.error.videoproject.document.delete.notfound')], Response::HTTP_NOT_FOUND);
        }
    }
}
