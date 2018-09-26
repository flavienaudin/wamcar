<?php

namespace AppBundle\Controller\Front\PersonalContext;


use AppBundle\Controller\Front\BaseController;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;

class AffinityController extends BaseController
{

    public function personalFormAction()
    {
        if (!$this->getUser() instanceof PersonalUser) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('front/Affinity/personal_form.html.twig');
    }


    public function proFormAction()
    {
        if (!$this->getUser() instanceof ProUser) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('front/Affinity/pro_form.html.twig');
    }

}