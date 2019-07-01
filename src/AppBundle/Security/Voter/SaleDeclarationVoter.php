<?php

namespace AppBundle\Security\Voter;


use AppBundle\Doctrine\Entity\ProApplicationUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wamcar\Sale\Declaration;

class SaleDeclarationVoter extends Voter
{
    const EDIT = 'saleDeclaration.edit';

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

        // only vote on Declaration objects inside this voter
        if (!$subject instanceof Declaration) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_PRO_ADMIN'))) {
            return true;
        }

        $user = $token->getUser();

        if (!$user instanceof ProApplicationUser) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Declaration object, thanks to supports
        /** @var Declaration $declaration */
        $declaration = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $declaration->getProUserSeller()->is($user);
        }

        throw new \LogicException('This code should not be reached!');
    }
}