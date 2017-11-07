<?php

namespace AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function documentationAction(Request $request): Response
    {
        die(sprintf('You are %s', $this->getUser() ? $this->getUser()->getUsername() : 'nobody'));
    }
}
