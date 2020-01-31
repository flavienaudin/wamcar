<?php


namespace AppBundle\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wamcar\User\VideosInsert;

class VideosInsertVoter extends Voter
{
    const EDIT = "videos_insert.edit";
    const DELETE = "videos_insert.delete";

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

        // only vote on VideosInsert objects inside this voter
        if (!$subject instanceof VideosInsert) {
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

        // you know $subject is a VideosInsert object, thanks to supports
        /** @var VideosInsert $videosInsert */
        $videosInsert = $subject;

        switch ($attribute) {
            case self::EDIT:
            case self::DELETE:
                return $videosInsert->getUser()->is($currentUser);
        }
        return false;
    }
}