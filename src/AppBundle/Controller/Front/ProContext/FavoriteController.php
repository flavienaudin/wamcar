<?php


namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Services\User\UserEditionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\User\BaseUser;
use Wamcar\User\ProUser;

class FavoriteController extends BaseController
{
    const FAVORITES_ALL = 'all';
    const FAVORITES_PRO = 'pro';
    const FAVORITES_PERSONAL = 'personal';

    /** @var UserEditionService */
    private $userEditionService;
    /** @var TranslatorInterface */
    private $translator;

    /**
     * FavoriteController constructor.
     * @param UserEditionService $userEditionService
     * @param TranslatorInterface $translator
     */
    public function __construct(UserEditionService $userEditionService, TranslatorInterface $translator)
    {
        $this->userEditionService = $userEditionService;
        $this->translator = $translator;
    }

    /**
     * security.yml - access_control : ROLE_USER required
     * @return Response
     */
    public function viewAction()
    {
        /** @var BaseUser $currentUser */
        $currentUser = $this->getUser();

        return $this->render('front/Favorites/user_favorites.html.twig', [
            'user_likes' => $currentUser->getPositiveLikes(),
            'user_experts' => $currentUser->getMyExperts(true)
        ]);
    }


    /**
     * @param Request $request
     * @param ProUser $expertUser
     * @return Response
     */
    public function toggleExpertAction(Request $request, ProUser $expertUser)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);

        /** @var BaseUser $currentUser */
        $currentUser = $this->getUser();
        $result = $this->userEditionService->toggleExpert($currentUser, $expertUser);
        if ($result) {
            $successMessage = $this->translator->trans('flash.success.expert.added');
        } else {
            $successMessage = $this->translator->trans('flash.success.expert.removed');
        }
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($successMessage);
        } else {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, $successMessage);
            return $this->redirectToRoute('front_view_pro_user_info', [
                'slug' => $expertUser->getSlug()
            ]);
        }
    }
}