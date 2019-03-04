<?php

namespace AppBundle\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wamcar\User\BaseUser;

class UserVoter extends Voter
{
    const EDIT = 'user.edit';
    const DELETE = 'user.delete';

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
        if (!in_array($attribute, [self::EDIT, self::DELETE])) {
            return false;
        }

        // only vote on BaseUser objects inside this voter
        if (!$subject instanceof BaseUser) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }

        $currentUser = $token->getUser();

        // you know $subject is a BaseUser object, thanks to supports
        /** @var BaseUser $user */
        $user = $subject;

        switch ($attribute) {
            case self::DELETE:
                return $user->is($currentUser);
        }

        return false;
    }

}