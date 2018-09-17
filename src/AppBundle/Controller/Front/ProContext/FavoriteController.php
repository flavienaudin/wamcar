<?php


namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use Symfony\Component\HttpFoundation\Response;

class FavoriteController extends BaseController
{
    const FAVORITES_ALL = 'all';
    const FAVORITES_PRO = 'pro';
    const FAVORITES_PERSONAL = 'personal';

    /**
     * security.yml - access_control : ROLE_USER required
     * @return Response
     */
    public function viewAction()
    {
        return $this->render('front/Favorites/user_favorites.html.twig', [
            'user_likes' => $this->getUser()->getPositiveLikes()
        ]);
    }

}