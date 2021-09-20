<?php


namespace AppBundle\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wamcar\User\ProUser;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectIteration;
use Wamcar\VideoCoaching\VideoProjectViewer;
use Wamcar\VideoCoaching\VideoVersion;

class VideoCoachingVoter extends Voter
{

    const MODULE_ACCESS = "video_coaching_module_access";

    const VIDEO_PROJECT_VIEW = "video_coaching_project.view";
    const VIDEO_PROJECT_ADD = "video_coaching_project.add";
    const VIDEO_PROJECT_EDIT = "video_coaching_project.edit";
    const VIDEO_PROJECT_DELETE = "video_coaching_project.delete";

    const VIDEO_PROJECT_ITERATION_ADD_VERSION = "video_coaching_project_iteration.add_version";
    const VIDEO_VERSION_EDIT = "video_coaching_video_version.edit";
    const VIDEO_VERSION_DELETE = "video_coaching_video_version.delete";

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

        // if the attribute is one we support with the correct subject type, return true
        if (in_array($attribute, [self::VIDEO_PROJECT_ITERATION_ADD_VERSION]) && $subject instanceof VideoProjectIteration) {
            return true;
        }

        // if the attribute is one we support with the correct subject type, return true
        if (in_array($attribute, [self::VIDEO_VERSION_EDIT, self::VIDEO_VERSION_DELETE]) && $subject instanceof VideoVersion) {
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
        } // Video coaching project management
        elseif (in_array($attribute, [self::VIDEO_PROJECT_VIEW, self::VIDEO_PROJECT_ADD, self::VIDEO_PROJECT_EDIT, self::VIDEO_PROJECT_DELETE])) {
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
        } // Video Project Iteration management
        elseif (in_array($attribute, [self::VIDEO_PROJECT_ITERATION_ADD_VERSION])) {
            // you know $subject is a VideoProjectIteration object, thanks to supports
            /** @var VideoProjectIteration $videoProjectIteration */
            $videoProjectIteration = $subject;

            /** @var ProUser $currentUser */
            $currentUser = $token->getUser();

            if ($currentUser instanceof ProUser) {
                switch ($attribute) {
                    case self::VIDEO_PROJECT_ITERATION_ADD_VERSION:
                        return $this->decisionManager->decide($token, [self::VIDEO_PROJECT_EDIT], $videoProjectIteration->getVideoProject()) && $videoProjectIteration->isLastIteration();
                }
            }
            return false;
        } // Video Version management
        elseif (in_array($attribute, [self::VIDEO_VERSION_EDIT, self::VIDEO_VERSION_DELETE])) {
            // you know $subject is a VideoProjectIteration object, thanks to supports
            /** @var VideoVersion $videoVersion */
            $videoVersion = $subject;

            /** @var ProUser $currentUser */
            $currentUser = $token->getUser();

            if ($currentUser instanceof ProUser) {
                switch ($attribute) {
                    case self::VIDEO_VERSION_EDIT:
                    case self::VIDEO_VERSION_DELETE:
                        return $this->decisionManager->decide($token, [self::VIDEO_PROJECT_EDIT], $videoVersion->getVideoProjectIteration()->getVideoProject()) && $videoVersion->getVideoProjectIteration()->isLastIteration();
                }
            }
        }
        return false;
    }
}