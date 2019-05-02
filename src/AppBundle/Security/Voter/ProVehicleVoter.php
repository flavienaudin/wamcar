<?php

namespace AppBundle\Security\Voter;

use AppBundle\Services\Vehicle\ProVehicleEditionService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\ProVehicle;

class ProVehicleVoter extends Voter
{
    const EDIT = 'pro_vehicle.edit';
    const DECLARE = 'pro_vehicle.declare';

    /** @var AccessDecisionManagerInterface */
    private $decisionManager;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;

    /**
     * ProVehicleVoter constructor.
     * @param AccessDecisionManagerInterface $decisionManager
     * @param ProVehicleEditionService $proVehicleEditionService
     */
    public function __construct(
        AccessDecisionManagerInterface $decisionManager,
        ProVehicleEditionService $proVehicleEditionService
    )
    {
        $this->decisionManager = $decisionManager;
        $this->proVehicleEditionService = $proVehicleEditionService;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT, self::DECLARE])) {
            return false;
        }

        // only vote on ProVehicle objects inside this voter
        if (!$subject instanceof ProVehicle) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }

        $user = $token->getUser();

        if (!$user instanceof ProUser) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a ProVehicle object, thanks to supports
        /** @var ProVehicle $proVehicle */
        $proVehicle = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->proVehicleEditionService->canEdit($user, $proVehicle);
            case self::DECLARE:
                return $this->proVehicleEditionService->canDeclareSale($user, $proVehicle);
        }

        throw new \LogicException('This code should not be reached!');
    }
}
