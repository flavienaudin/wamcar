<?php

namespace AppBundle\Controller\Front\PersonalContext;


use AppBundle\Controller\Front\BaseController;
use Symfony\Component\HttpFoundation\Request;

class AffinityController extends BaseController
{

    public function personalFormAction(Request $request)
    {
        /*if (!$this->getUser() instanceof PersonalUser) {
            throw $this->createAccessDeniedException();
        }*/
        return $this->render('front/Affinity/personalForm.html.twig');
    }


    public function proFormAction(Request $request)
    {
        /*if (!$this->getUser() instanceof ProUser) {
            throw $this->createAccessDeniedException();
        }*/
        return $this->render('front/Affinity/proForm.html.twig');
    }

}