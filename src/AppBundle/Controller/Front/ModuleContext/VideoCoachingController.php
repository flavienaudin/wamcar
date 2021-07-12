<?php


namespace AppBundle\Controller\Front\ModuleContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Form\DTO\VideoProjectDTO;
use AppBundle\Form\DTO\VideoVersionDTO;
use AppBundle\Form\Type\VideoProjectType;
use AppBundle\Form\Type\VideoVersionType;
use AppBundle\Security\Voter\VideoCoachingVoter;
use AppBundle\Services\VideoCoaching\VideoProjectService;
use AppBundle\Services\VideoCoaching\VideoVersionService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, VideoProject $videoProject)
    {
        if (!$this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_VIEW, $videoProject)) {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, 'flash.error.unauthorized.video_coaching.video_project.view');
            return $this->redirectToRoute('front_coaching_video_dashboard');
        }

        $editVideoProjectForm = null;
        $createVideoVersionForm = null;
        $editVideoVersionFormViews = [];

        // Formulaire d'édition du projet vidéo
        if ($this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_EDIT, $videoProject)) {
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
        }


        return $this->render('front/VideoCoaching/VideoProject/view.html.twig', [
            'videoProject' => $videoProject,
            'editVideoProjectForm' => $editVideoProjectForm ? $editVideoProjectForm->createView() : null,
            'createVideoVersionForm' => $createVideoVersionForm ? $createVideoVersionForm->createView() : null,
            'editVideoVersionForms' => $editVideoVersionFormViews
        ]);
    }

    /**
     * @param VideoProject $videoProject
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(VideoProject $videoProject)
    {
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteVideoVersionAction(VideoVersion $videoVersion)
    {
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

    public function getMessagesAction(VideoProject $videoProject, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        if (!$this->isGranted(VideoCoachingVoter::VIDEO_PROJECT_VIEW, $videoProject)) {
            return new JsonResponse($this->translator->trans('flash.error.unauthorized.video_coaching.video_project.view'), Response::HTTP_FORBIDDEN);
        }

        $start = $request->get('start', null);
        $end = $request->get('end', null);

        dump($start);
        dump($end);

        // TODO récupérer les messages entre "start" si fourni et "end"
        return new JsonResponse([
            "start" => $start,
            "end" => $end,
            "messages" => []
        ]);

    }
}