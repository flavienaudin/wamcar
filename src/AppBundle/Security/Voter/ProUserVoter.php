<?php

namespace AppBundle\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wamcar\User\ProUser;

class ProUserVoter extends Voter
{
    const EDIT = 'user.edit';

    /** @var AccessDecisionManagerInterface */
    private $decisionManager;

    /**
     * GarageVoter constructor.
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT])) {
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
        if ($this->decisionManager->decide($token, array('ROLE_PRO_ADMIN'))) {
            return true;
        }

        $currentUser = $token->getUser();

        // you know $subject is a ProUser object, thanks to supports
        /** @var ProUser $user */
        $user = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $user->is($currentUser);
        }

        return false;
    }

}