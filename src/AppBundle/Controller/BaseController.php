<?php


namespace AppBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


abstract class BaseController
{
    const FLASH_LEVEL_SUCCESS = 'success';
    const FLASH_LEVEL_INFO = 'info';
    const FLASH_LEVEL_WARNING = 'warning';
    const FLASH_LEVEL_DANGER = 'alert';

    /** @var LoggerInterface */
    protected $logger;

    /** @var EngineInterface */
    protected $templatingEngine;

    /** @var RouterInterface */
    protected $router;

    /** @var Session */
    protected $session;

    /** @var TokenStorageInterface */
    protected $tokenStorage;


    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

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
     * @param Session $session
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Adds a flash message to the current session for type.
     *
     * @param string $message The message
     * @param string $type The type
     *
     * @throws \InvalidArgumentException
     */
    protected function addFlash(string $message, string $type = self::FLASH_LEVEL_INFO)
    {
        if (!in_array($type, self::getAvailableFlashLevels())) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid '%s' type for flash message. Available values are %s.",
                    $type,
                    implode(', ', self::getAvailableFlashLevels())
                )
            );
        }

        $this->session->getFlashBag()->add($type, $message);
    }


    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string $url    The URL to redirect to
     * @param int    $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param string $route      The name of the route
     * @param array  $parameters An array of parameters
     * @param int    $status     The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirectToRoute($route, array $parameters = array(), $status = 302)
    {
        return $this->redirect($this->router->generate($route, $parameters), $status);
    }

    /**
     * @return array
     */
    private static function getAvailableFlashLevels(): array
    {
        return [
            self::FLASH_LEVEL_INFO,
            self::FLASH_LEVEL_SUCCESS,
            self::FLASH_LEVEL_WARNING,
            self::FLASH_LEVEL_DANGER,
        ];
    }

}
