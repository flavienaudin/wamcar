<?php


namespace AppBundle\Security\Voter;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wamcar\User\ProUser;
use Wamcar\VideoCoaching\ScriptVersion;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectDocument;
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

    const VIDEO_PROJECT_ITERATION_ADD_VIDEOVERSION = "video_coaching_project_iteration.add_videoversion";
    const VIDEO_PROJECT_ITERATION_ADD_SCRIPTVERSION = "video_coaching_project_iteration.add_scriptversion";

    const SCRIPT_VERSION_EDIT = "video_coaching_script_version.edit";
    const SCRIPT_VERSION_DELETE = "video_coaching_script_version.delete";

    const VIDEO_VERSION_EDIT = "video_coaching_video_version.edit";
    const VIDEO_VERSION_DELETE = "video_coaching_video_version.delete";

    const LIBRARY_ADD_DOCUMENT = "video_coaching.library.add_document";
    const LIBRARY_DELETE_DOCUMENT = "video_coaching.library.delete_document";

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
        if (in_array($attribute, [self::MODULE_ACCESS, self::VIDEO_PROJECT_ADD]) && $subject instanceof ProUser) {
            return true;
        }

        // if the attribute is one we support with the correct subject type, return true
        if (in_array($attribute, [self::VIDEO_PROJECT_VIEW, self::VIDEO_PROJECT_EDIT, self::VIDEO_PROJECT_DELETE, self::LIBRARY_ADD_DOCUMENT]) && $subject instanceof VideoProject) {
            return true;
        }

        // if the attribute is one we support with the correct subject type, return true
        if (in_array($attribute, [self::VIDEO_PROJECT_ITERATION_ADD_VIDEOVERSION, self::VIDEO_PROJECT_ITERATION_ADD_SCRIPTVERSION]) && $subject instanceof VideoProjectIteration) {
            return true;
        }

        // if the attribute is one we support with the correct subject type, return true
        if (in_array($attribute, [self::SCRIPT_VERSION_EDIT, self::SCRIPT_VERSION_DELETE]) && $subject instanceof ScriptVersion) {
            return true;
        }

        // if the attribute is one we support with the correct subject type, return true
        if (in_array($attribute, [self::VIDEO_VERSION_EDIT, self::VIDEO_VERSION_DELETE]) && $subject instanceof VideoVersion) {
            return true;
        }

        // if the attribute is one we support with the correct subject type, return true
        if (in_array($attribute, [self::LIBRARY_DELETE_DOCUMENT]) && $subject instanceof VideoProjectDocument) {
            return true;
        }

        return false;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($this->decisionManager->decide($token, array('ROLE_PRO_ADMIN'))) {
            return true;
        }

        /** @var ProUser $currentUser */
        $currentUser = $token->getUser();
        if (!$currentUser instanceof ProUser) {
            return false;
        }

        // Video Coaching Module access
        if (in_array($attribute, [self::MODULE_ACCESS])) {
            // Autorisation en version restreinte pour tous
            return true;
            // you know $subject is a ProUser object, thanks to supports
            /** @var ProUser $proUser */
            // $proUser = $subject;
            //$proUser->hasVideoModuleAccess();
        }// Video coaching project creation
        elseif (in_array($attribute, [self::VIDEO_PROJECT_ADD])) {
            // you know $subject is a ProUser object, thanks to supports
            /** @var ProUser $proUser */
            $proUser = $subject;

            if (!$proUser->is($token->getUser())) {
                return false;
            }

            // Abonnement payant ou restreint sans projet déjà créé
            return $proUser->hasVideoModuleAccess() || $proUser->getCreatedVideoProjects()->isEmpty();
        } // Video coaching project management
        elseif (in_array($attribute, [self::VIDEO_PROJECT_VIEW, self::VIDEO_PROJECT_EDIT, self::VIDEO_PROJECT_DELETE])) {
            // you know $subject is a VideoProject object, thanks to supports
            /** @var VideoProject $videoProject */
            $videoProject = $subject;

            switch ($attribute) {
                case self::VIDEO_PROJECT_VIEW:
                    /** @var VideoProjectViewer $videoProjectViewer */
                    foreach ($videoProject->getViewers() as $videoProjectViewer) {
                        if ($videoProjectViewer->getViewer()->is($currentUser)) {
                            return true;
                        }
                    }
                    return false;
                case self::VIDEO_PROJECT_EDIT:
                case self::VIDEO_PROJECT_DELETE:
                    /** @var VideoProjectViewer $videoProjectCreators */
                    foreach ($videoProject->getCreators() as $videoProjectCreators) {
                        if ($videoProjectCreators->getViewer()->is($currentUser)) {
                            return true;
                        }
                    }
                    return false;
            }
        } // Video Project Iteration management
        elseif (in_array($attribute, [self::VIDEO_PROJECT_ITERATION_ADD_VIDEOVERSION, self::VIDEO_PROJECT_ITERATION_ADD_SCRIPTVERSION])) {
            // you know $subject is a VideoProjectIteration object, thanks to supports
            /** @var VideoProjectIteration $videoProjectIteration */
            $videoProjectIteration = $subject;

            switch ($attribute) {
                case self::VIDEO_PROJECT_ITERATION_ADD_VIDEOVERSION:
                case self::VIDEO_PROJECT_ITERATION_ADD_SCRIPTVERSION:
                    return $this->decisionManager->decide($token, [self::VIDEO_PROJECT_EDIT], $videoProjectIteration->getVideoProject()) && $videoProjectIteration->isLastIteration();
            }
            return false;
        } // Script Version management
        elseif (in_array($attribute, [self::SCRIPT_VERSION_EDIT, self::SCRIPT_VERSION_DELETE])) {
            // you know $subject is a VideoProjectIteration object, thanks to supports
            /** @var ScriptVersion $scriptVersion */
            $scriptVersion = $subject;

            switch ($attribute) {
                case self::SCRIPT_VERSION_EDIT:
                    return $this->decisionManager->decide($token, [self::VIDEO_PROJECT_EDIT], $scriptVersion->getVideoProjectIteration()->getVideoProject())
                        && $scriptVersion->getVideoProjectIteration()->isLastIteration()
                        && $scriptVersion->isLastScriptVersion();
                case self::SCRIPT_VERSION_DELETE:
                    return $this->decisionManager->decide($token, [self::VIDEO_PROJECT_EDIT], $scriptVersion->getVideoProjectIteration()->getVideoProject())
                        && $scriptVersion->getVideoProjectIteration()->isLastIteration();
            }
        } // Video Version management
        elseif (in_array($attribute, [self::VIDEO_VERSION_EDIT, self::VIDEO_VERSION_DELETE])) {
            // you know $subject is a VideoVersion object, thanks to supports
            /** @var VideoVersion $videoVersion */
            $videoVersion = $subject;

            switch ($attribute) {
                case self::VIDEO_VERSION_EDIT:
                case self::VIDEO_VERSION_DELETE:
                    return $this->decisionManager->decide($token, [self::VIDEO_PROJECT_EDIT], $videoVersion->getVideoProjectIteration()->getVideoProject()) && $videoVersion->getVideoProjectIteration()->isLastIteration();
            }
        }// Library access and management
        elseif (in_array($attribute, [self::LIBRARY_ADD_DOCUMENT])) {
            // you know $subject is a VideoProject object, thanks to supports
            /** @var VideoProject $videoProject */
            $videoProject = $subject;
            switch ($attribute) {
                case self::LIBRARY_ADD_DOCUMENT:
                    return $this->decisionManager->decide($token, [self::VIDEO_PROJECT_VIEW], $videoProject);
            }
        } elseif (in_array($attribute, [self::LIBRARY_DELETE_DOCUMENT])) {
            // you know $subject is a VideoProjectDocument object, thanks to supports
            /** @var VideoProjectDocument $videoProjectDocument */
            $videoProjectDocument = $subject;
            switch ($attribute) {
                case self::LIBRARY_DELETE_DOCUMENT:
                    $currentUserViewerInfo = $videoProjectDocument->getVideoProject()->getViewerInfo($currentUser);
                    return $videoProjectDocument->getOwnerViewer()->getViewer()->is($currentUser) || ($currentUserViewerInfo != false && $currentUserViewerInfo->isCreator());
            }
        }
        return false;
    }
}
