<?php


namespace AppBundle\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wamcar\User\ProUser;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectViewer;

class VideoCoachingVoter extends Voter
{

    const MODULE_ACCESS = "video_coaching_module_access";
    const VIDEO_PROJECT_VIEW = "video_coaching_project.view";
    const VIDEO_PROJECT_ADD = "video_coaching_project.add";
    const VIDEO_PROJECT_EDIT = "video_coaching_project.edit";
    const VIDEO_PROJECT_DELETE = "video_coaching_project.delete";

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
        // if the attribute is one we support with the correct subject type, return true
        if (in_array($attribute, [self::MODULE_ACCESS]) && $subject instanceof ProUser) {
            return true;
        }

        // if the attribute is one we support with the correct subject type, return true
        if (in_array($attribute, [self::VIDEO_PROJECT_VIEW, self::VIDEO_PROJECT_ADD, self::VIDEO_PROJECT_EDIT, self::VIDEO_PROJECT_DELETE]) && $subject instanceof VideoProject) {
            return true;
        }

        return false;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_PRO_ADMIN'))) {
            return true;
        }

        // Video Coaching Module access
        if (in_array($attribute, [self::MODULE_ACCESS])) {
            // you know $subject is a ProUser object, thanks to supports
            /** @var ProUser $proUser */
            $proUser = $subject;
            return $proUser->hasVideoModuleAccess();
        }

        // Video coaching project management
        if (in_array($attribute, [self::VIDEO_PROJECT_VIEW, self::VIDEO_PROJECT_ADD, self::VIDEO_PROJECT_EDIT, self::VIDEO_PROJECT_DELETE])) {
            // you know $subject is a VideoProject object, thanks to supports
            /** @var VideoProject $videoProject */
            $videoProject = $subject;

            /** @var ProUser $currentUser */
            $currentUser = $token->getUser();

            if ($currentUser instanceof ProUser) {
                switch ($attribute) {
                    case self::VIDEO_PROJECT_VIEW:
                        /** @var VideoProjectViewer $videoProjectCreators */
                        foreach ($videoProject->getViewers() as $videoProjectCreators) {
                            if ($videoProjectCreators->getViewer()->is($currentUser)) {
                                return true;
                            }
                        }
                        return false;
                    case self::VIDEO_PROJECT_ADD:
                    case self::VIDEO_PROJECT_EDIT:
                    case self::VIDEO_PROJECT_DELETE:
                        /** @var VideoProjectViewer $videoProjectViewer */
                        foreach ($videoProject->getCreators() as $videoProjectCreators) {
                            if ($videoProjectCreators->getViewer()->is($currentUser)) {
                                return true;
                            }
                        }
                        return false;
                }
            }
        }
        return false;
    }
}