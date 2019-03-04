<?php

namespace AppBundle\Security\Voter;

use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Services\Garage\GarageEditionService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wamcar\Garage\Garage;

class GarageVoter extends Voter
{
    const EDIT = 'garage.edit';
    const ADMINISTRATE = 'garage.administrate';

    /** @var AccessDecisionManagerInterface */
    private $decisionManager;
    /** @var GarageEditionService */
    private $garageEditionService;

    /**
     * GarageVoter constructor.
     * @param AccessDecisionManagerInterface $decisionManager
     * @param GarageEditionService $garageEditionService
     */
    public function __construct(
        AccessDecisionManagerInterface $decisionManager,
        GarageEditionService $garageEditionService
    )
    {
        $this->decisionManager = $decisionManager;
        $this->garageEditionService = $garageEditionService;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT, self::ADMINISTRATE])) {
            return false;
        }

        // only vote on Garage objects inside this voter
        if (!$subject instanceof Garage) {
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

        if (!$user instanceof ProApplicationUser) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Garage object, thanks to supports
        /** @var Garage $garage */
        $garage = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->garageEditionService->canEdit($user, $garage);
            case self::ADMINISTRATE:
                return $this->garageEditionService->canAdministrate($user, $garage);
        }

        throw new \LogicException('This code should not be reached!');
    }
}
