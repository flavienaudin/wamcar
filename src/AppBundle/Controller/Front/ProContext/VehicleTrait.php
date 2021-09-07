<?php


namespace AppBundle\Controller\Front\ProContext;


use Symfony\Component\HttpFoundation\RedirectResponse;

trait VehicleTrait
{
    /**
     * @param array $routeParam Paramètres additionnel pour la route sauvegardée en session
     * @param string $routeNoSession Route par défaut s'il n'y a pas de route en session
     * @param array $routeParamNoSession Paramètres pour la route par défaut
     * @return RedirectResponse
     */
    protected function redirSave(array $routeParam, string $routeNoSession, array $routeParamNoSession = []): RedirectResponse
    {
        // Get the SessionMessage data from the session :
        // - utiliser lors de l'écriture d'un message avec création d'un véhicule pour revenir vers la messagerie avec le brouillon
        $sessionMessage = $this->sessionMessageManager->get();
        if ($sessionMessage) {
            return $this->redirectToRoute($sessionMessage->route, array_merge($sessionMessage->routeParams, $routeParam));
        }
        return $this->redirectToRoute($routeNoSession, $routeParamNoSession);
    }
}
