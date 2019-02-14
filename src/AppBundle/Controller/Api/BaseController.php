<?php

namespace AppBundle\Controller\Api;

use AppBundle\Doctrine\Entity\ApplicationUser;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Wamcar\Garage\Garage;

/**
 * @SWG\Swagger(
 *     host="wamcar.com",
 *     basePath="/v1",
 *     produces={"application/json"},
 *     consumes={"application/json"},
 *     @SWG\Info(
 *         version="v1",
 *         title="API Wamcar",
 *         description="API permettant de réaliser des opérations sur wamcar en tant que professionel",
 *     )
 *  )
 */
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
