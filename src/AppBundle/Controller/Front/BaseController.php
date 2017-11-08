<?php

namespace AppBundle\Controller\Front;

use AppBundle\Doctrine\Entity\ApplicationUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

abstract class BaseController
{
    const FLASH_LEVEL_INFO = 'info';
    const FLASH_LEVEL_WARNING = 'warning';
    const FLASH_LEVEL_DANGER = 'danger';

    /** @var EngineInterface */
    protected $templatingEngine;

    /** @var RouterInterface */
    protected $router;

    /** @var SessionInterface */
    protected $session;

    /** @var TokenStorageInterface */
    protected $tokenStorage;


    /**
     * @param EngineInterface $templatingEngine
     */
    public function setTemplatingEngine(EngineInterface $templatingEngine)
    {
        $this->templatingEngine = $templatingEngine;
    }

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
     * @param TokenStorageInterface $tokenStorage
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Renders a view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     * @param Response $response A response instance
     *
     * @return Response A Response instance
     */
    protected function render(string $view, array $parameters = array(), Response $response = null): Response
    {
        return $this->templatingEngine->renderResponse($view, $parameters, $response);
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string $url The URL to redirect to
     * @param int $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirect($url, $status = Response::HTTP_FOUND): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param string $route The name of the route
     * @param array $parameters An array of parameters
     * @param int $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirectToRoute(
        string $route,
        array $parameters = array(),
        int $status = Response::HTTP_FOUND): RedirectResponse
    {
        $anchor = null;
        if (isset($parameters['#'])) {
            $anchor = '#' . $parameters['#'];
            unset($parameters['#']);
        }

        return $this->redirect($this->router->generate($route, $parameters) . $anchor, $status);
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

    /**
     * Return the connected user if someone is connected, or null otherwise
     *
     * @return ApplicationUser|null
     */
    protected function getUser()
    {
        $token = $this->tokenStorage->getToken();
        if (is_object($token->getUser())) {
            return $token->getUser();
        }
        return null;
    }

    /**
     * @return bool
     */
    protected function isUserAuthenticated(): bool
    {
        return $this->tokenStorage->getToken() !== null && $this->tokenStorage->getToken()->isAuthenticated();
    }

}
