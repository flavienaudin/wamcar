<?php


namespace AppBundle\Controller\Front\ProContext;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Wamcar\Vehicle\BaseVehicle;

trait VehicleTrait
{
    /**
     * @param BaseVehicle $vehicle
     * @param string $routeNoSession
     * @return RedirectResponse
     */
    protected function redirSave(BaseVehicle $vehicle, string $routeNoSession): RedirectResponse
    {
        $sessionMessage = $this->sessionMessageManager->get();
        if ($sessionMessage) {
            return $this->redirectToRoute($sessionMessage->route, array_merge($sessionMessage->routeParams, ['v' => $vehicle->getId(), '_fragment' => 'message-answer-block']));
        }
        return $this->redirectToRoute($routeNoSession, ['id' => $vehicle->getId()]);
    }
}
