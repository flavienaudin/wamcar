<?php

namespace AppBundle\Controller\Front\AdministrationContext;


use AppBundle\Controller\Front\BaseController;

class AdministrationController extends BaseController
{

    public function adminBoardAction()
    {
        return $this->render('front/adminContext/administration_board.html.twig');
    }

}