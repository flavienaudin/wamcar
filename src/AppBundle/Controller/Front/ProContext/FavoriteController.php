<?php


namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class FavoriteController extends BaseController
{
    const FAVORITES_ALL = 'all';
    const FAVORITES_PRO = 'pro';
    const FAVORITES_PERSONAL = 'personal';

    public function viewAction()
    {
        if (!$this->isUserAuthenticated()) {
            throw new AccessDeniedException();
        }

        return $this->render('front/Favorites/user_favorites.html.twig', [
            'user_likes' => $this->getUser()->getPositiveLikes()
        ]);
    }

}