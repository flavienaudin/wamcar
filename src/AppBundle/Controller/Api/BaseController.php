<?php

namespace AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;


abstract class BaseController
{
    /** @var RouterInterface */
    protected $router;
    /** @var SessionInterface */
    protected $session;

    /**
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Returns url of the given route with the given parameters.
     *
     * @param string $routeName
     * @param array $routeParameters
     *
     * @return string
     */
    protected function generateRoute(string $routeName, array $routeParameters = []): string
    {
        return $this->router->generate($routeName, $routeParameters);
    }
}
