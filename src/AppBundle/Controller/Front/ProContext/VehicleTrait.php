<?php


namespace AppBundle\Controller\Front\ProContext;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Wamcar\Vehicle\BaseVehicle;

trait VehicleTrait
{
    /**
     * @param array $routeParam Paramètres additionnel pour la route sauvegardée en session
     * @param string $routeNoSession Route par défaut s'il n'y a pas de route en session
     * @param array $routeParamNoSession Paramètres pour la route par défaut
     * @return RedirectResponse
     */
    protected function redirSave(array $routeParam = [], string $routeNoSession, array $routeParamNoSession = []): RedirectResponse
    {
        $sessionMessage = $this->sessionMessageManager->get();
        if ($sessionMessage) {
            return $this->redirectToRoute($sessionMessage->route, array_merge($sessionMessage->routeParams, $routeParam));
        }
        return $this->redirectToRoute($routeNoSession, $routeParamNoSession);
    }
}
