<?php


namespace AppBundle\Security\Voter;


use AppBundle\Doctrine\Entity\ProApplicationUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wamcar\Garage\GarageProUser;
use Wamcar\User\ProUser;

class SellerPerformancesVoter extends Voter
{
    const SHOW = 'sellerPerformance.show';

    /** @var AccessDecisionManagerInterface */
    private $decisionManager;

    /**
     * GarageVoter constructor.
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(
        AccessDecisionManagerInterface $decisionManager
    )
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::SHOW])) {
            return false;
        }

        // only vote on ProUser objects inside this voter
        if (!$subject instanceof ProUser) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        $currentUser = $token->getUser();
        if (!$currentUser instanceof ProApplicationUser) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a ProUser object, thanks to supports
        /** @var ProUser $seller */
        $seller = $subject;

        switch ($attribute) {
            case self::SHOW:
                if ($seller->is($currentUser)) {
                    return true;
                }

                /** @var GarageProUser $garageMembership */
                foreach ($seller->getEnabledGarageMemberships() as $garageMembership) {
                    if ($currentUser->isAdministratorOfGarage($garageMembership->getGarage())) {
                        return true;
                    }
                }
                return false;
                break;
        }

        throw new \LogicException('This code should not be reached!');
    }
}