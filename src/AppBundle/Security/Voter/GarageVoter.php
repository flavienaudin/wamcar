<?php

namespace AppBundle\Security\Voter;

use AppBundle\Services\Garage\GarageEditionService;
use Wamcar\Garage\Garage;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GarageVoter extends Voter
{
    // these strings are just invented: you can use anything
    const EDIT = 'edit';

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
        if (self::EDIT !== $attribute) {
            return false;
        }

        // only vote on Post objects inside this voter
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
        }

        throw new \LogicException('This code should not be reached!');
    }
}
