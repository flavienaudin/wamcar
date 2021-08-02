<?php


namespace AppBundle\Controller\Front\ModuleContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\VideoProjectDTO;
use AppBundle\Form\DTO\VideoProjectMessageDTO;
use AppBundle\Form\DTO\VideoVersionDTO;
use AppBundle\Form\Type\VideoProjectMessageType;
use AppBundle\Form\Type\VideoProjectShareType;
use AppBundle\Form\Type\VideoProjectType;
use AppBundle\Form\Type\VideoVersionType;
use AppBundle\Security\Voter\VideoCoachingVoter;
use AppBundle\Services\VideoCoaching\VideoProjectService;
use AppBundle\Services\VideoCoaching\VideoVersionService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\User\ProUser;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoVersion;

class VideoCoachingController extends BaseController
{
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var TranslatorInterface $translator */
    private $translator;
    /** @var VideoProjectService */
    private $videoProjectService;
    /** @var VideoVersionService */
    private $videoVersionService;


    public function __construct(FormFactoryInterface $formFactory, TranslatorInterface $translator, VideoProjectService $videoProjectService, VideoVersionService $videoVersionService)
    {
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->videoProjectService = $videoProjectService;
        $this->videoVersionService = $videoVersionService;
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function dashboardAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);

        /** @var ProUser $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted(VideoCoachingVoter::MODULE_ACCESS, $currentUser)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.module_access');
            return $this->redirectToRoute('front_view_current_user_info');
        }

        // Formulaire de création d'un projet vidéo
        $videoProjectDTO = new VideoProjectDTO();
        $createdVideoProjectForm = $this->formFactory->create(VideoProjectType::class, $videoProjectDTO);
        $createdVideoProjectForm->handleRequest($request);
        if ($createdVideoProjectForm->isSubmitted() && $createdVideoProjectForm->isValid()) {
            $videoProject = $this->videoProjectService->create($videoProjectDTO, $currentUser);
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.videoproject.save');
            return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                'id' => $videoProject->getId()
            ]);
        }

        return $this->render('front/VideoCoaching/dashboard.html.twig', [
            'createdVideoProjectViewers' => $currentUser->getCreatedVideoProjects(),
            'followedVideoProjectViewers' => $currentUser->getFollowedVideoProjects(),
            'createdVideoProjectForm' => $createdVideoProjectForm ? $createdVideoProjectForm->createView() : null

        ]);
    }

    /**
     * @param Request $request
     * @param VideoProject $videoProject
     * @return RedirectResponse|Response
     */
    public function viewAction(Request $request, VideoProject $videoProject)
    {
        /** @var ProUser $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted(VideoCoachingVoter::MODULE_ACCESS, $currentUser)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.module_access');
            return $this->redirectToRoute('front_view_current_user_info');
        }

        if (!$this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_VIEW, $videoProject)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.video_project.view');
            return $this->redirectToRoute('front_coaching_video_dashboard');
        }

        $editVideoProjectForm = null;
        $createVideoVersionForm = null;
        $editVideoVersionFormViews = [];
        $shareVideoProjectForm = null;
        // Formulaire d'édition du projet vidéo
        if ($this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_EDIT, $videoProject)) {
            // Formulaire d'édition des informations du projet video (title, description)
            $videoProjectDTO = VideoProjectDTO::buildFromVideoProject($videoProject);
            $editVideoProjectForm = $this->formFactory->create(VideoProjectType::class, $videoProjectDTO);
            $editVideoProjectForm->handleRequest($request);
            if ($editVideoProjectForm->isSubmitted() && $editVideoProjectForm->isValid()) {
                $videoProject = $this->videoProjectService->update($videoProjectDTO, $videoProject);
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.videoproject.save');
                return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                    'id' => $videoProject->getId()
                ]);
            }

            // Formulaire d'ajout d'une nouvelle version au projet
            $videoVersionDTO = new VideoVersionDTO();
            $createVideoVersionForm = $this->formFactory->createNamed('addVideoVersion', VideoVersionType::class, $videoVersionDTO);
            $createVideoVersionForm->handleRequest($request);
            if ($createVideoVersionForm->isSubmitted() && $createVideoVersionForm->isValid()) {
                $this->videoVersionService->create($videoVersionDTO, $videoProject);
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.videoversion.save');
                return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                    'id' => $videoProject->getId()
                ]);
            }

            // Formulaire d'édition des versions de la vidéo
            /** @var VideoVersion $videoVersion */
            foreach ($videoProject->getVideoVersions() as $videoVersion) {
                $videoVersionDTO = VideoVersionDTO::buildFromVideoVersion($videoVersion);
                $editVideoVersionForm = $this->formFactory->createNamed('editVideoVersion' . $videoVersion->getId(), VideoVersionType::class, $videoVersionDTO);
                $editVideoVersionForm->handleRequest($request);
                if ($editVideoVersionForm->isSubmitted() && $editVideoVersionForm->isValid()) {
                    $this->videoVersionService->update($videoVersionDTO, $videoVersion);
                    $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.videoversion.update');
                    return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                        'id' => $videoProject->getId()
                    ]);
                }
                $editVideoVersionFormViews[$videoVersion->getId()] = $editVideoVersionForm->createView();
            }

            // Formulaire de partage du projet vidéo avec d'autres utilisateurs
            $shareVideoProjectForm = $this->formFactory->create(VideoProjectShareType::class);
            $shareVideoProjectForm->handleRequest($request);
            if ($shareVideoProjectForm->isSubmitted() && $shareVideoProjectForm->isValid()) {
                $results = $this->videoProjectService->shareVideoProjectToUsersByEmails($videoProject, $shareVideoProjectForm->get('emails')->getData());
                if (count($results[VideoProjectService::VIDEOCOACHING_SHARE_VIDEOPROJECT_FAIL]) > 0) {
                    $emailsNotFoundList = join(', ', array_values($results[VideoProjectService::VIDEOCOACHING_SHARE_VIDEOPROJECT_FAIL]));
                    $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING,
                        $this->translator->transChoice('flash.warning.video_project.share.not_found',
                            count($results[VideoProjectService::VIDEOCOACHING_SHARE_VIDEOPROJECT_FAIL]),
                            ['%prousers_notfound_list%' => $emailsNotFoundList]
                        ));
                }

                return $this->redirectToRoute('front_coachingvideo_videoproject_view', ['id' => $videoProject->getId()]);
            }
        }

        // Form submission handle in dedicated action for ajax management
        $messageForm = $this->formFactory->create(VideoProjectMessageType::class, new VideoProjectMessageDTO($videoProject, $currentUser));

        return $this->render('front/VideoCoaching/VideoProject/view.html.twig', [
            'videoProject' => $videoProject,
            'editVideoProjectForm' => $editVideoProjectForm ? $editVideoProjectForm->createView() : null,
            'createVideoVersionForm' => $createVideoVersionForm ? $createVideoVersionForm->createView() : null,
            'editVideoVersionForms' => $editVideoVersionFormViews,
            'discussionMessageForm' => $messageForm ? $messageForm->createView() : null,
            'shareVideoProjectForm' => $shareVideoProjectForm ? $shareVideoProjectForm->createView() : null
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
            return $this->redirectToRoute('front_coaching_video_dashboard');
        }
        $this->videoProjectService->delete($videoProject);
        $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.videoproject.delete');
        return $this->redirectToRoute('front_coaching_video_dashboard');
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

        $videoProject = $videoVersion->getVideoProject();
        if (!$this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_EDIT, $videoProject)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.video_version.delete');
            return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
                'id' => $videoProject->getId()
            ]);
        }

        $this->videoVersionService->delete($videoVersion);
        $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.videoversion.delete');
        return $this->redirectToRoute('front_coachingvideo_videoproject_view', [
            'id' => $videoProject->getId()
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
                        'messageForm' => $messageForm ? $messageForm->createView() : null,
                        'formClass' => 'form-compact row'
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

        $messages = $this->videoProjectService->getMessages($videoProject, $start, $end);
        return new JsonResponse([
            "start" => $start ? $start->getTimestamp() : null,
            "end" => $end->getTimestamp(),
            "messages" => $this->renderTemplate('front/VideoCoaching/VideoProject/Messages/includes/view.html.twig', [
                'messages' => $messages,
                'videoProjectViewer' => $videoProject->getViewerInfo($currentUser)
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
}