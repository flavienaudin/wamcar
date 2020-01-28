<?php

namespace AppBundle\Controller\Front;

use AppBundle\Doctrine\Entity\ApplicationUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;


abstract class BaseController
{
    const FLASH_LEVEL_INFO = 'success';
    const FLASH_LEVEL_WARNING = 'warning';
    const FLASH_LEVEL_DANGER = 'alert';

    const REQUEST_HEADER_REFERER = 'referer';

    const LIKE_REDIRECT_TO_SESSION_KEY = "vehicle.like.target_path";

    /** @var EngineInterface */
    protected $templatingEngine;

    /** @var RouterInterface */
    protected $router;

    /** @var SessionInterface */
    protected $session;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

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
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
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
     * REnder a template
     * @param string $view
     * @param array $parameters
     * @return string
     */
    protected function renderTemplate(string $view, array $parameters = array()): string {
        return $this->templatingEngine->render($view, $parameters);
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
     * Returns url of the given route with the given parameters.
     *
     * @param string $routeName
     * @param array $routeParameters
     *
     * @return string
     */
    protected function generateUrl(string $routeName, array $routeParameters = []): string
    {
        return $this->router->generate($routeName, $routeParameters, UrlGeneratorInterface::ABSOLUTE_URL);
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
        return $this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
    }

    /**
     * @return bool
     */
    protected function isUserAuthenticatedFully(): bool
    {
        return $this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY);
    }


    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied object.
     *
     * @param mixed $attributes The attributes
     * @param mixed $object The object
     *
     * @return bool
     */
    protected function isGranted($attributes, $object = null)
    {
        return $this->authorizationChecker->isGranted($attributes, $object);
    }

    /**
     * Throws an exception unless the attributes are granted against the current authentication token and optionally
     * supplied object.
     *
     * @param mixed $attributes The attributes
     * @param mixed $object The object
     * @param string $message The message passed to the exception
     *
     * @throws AccessDeniedException
     */
    protected function denyAccessUnlessGranted($attributes, $object = null, $message = null)
    {
        if (!$this->isGranted($attributes, $object)) {
            if(!empty($message)){
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_WARNING, $message);
            }
            $exception = $this->createAccessDeniedException($message);
            $exception->setAttributes($attributes);
            $exception->setSubject($object);

            throw $exception;
        }
    }

    /**
     * Returns an AccessDeniedException.
     *
     * This will result in a 403 response code. Usage example:
     *
     *     throw $this->createAccessDeniedException('Unable to access this page!');
     *
     * @param string $message A message
     * @param \Exception|null $previous The previous exception
     *
     * @return AccessDeniedException
     */
    protected function createAccessDeniedException($message = 'Access Denied.', \Exception $previous = null)
    {
        return new AccessDeniedException($message, $previous);
    }

    /**
     * @param Request $request
     * @return null|string|string[]
     */
    public function getReferer(Request $request)
    {
        return $request->headers->get(self::REQUEST_HEADER_REFERER);
    }
}
