<?php

namespace AppBundle\Controller\Front\PersonalContext;


use AppBundle\Controller\Front\BaseController;
use Wamcar\User\BaseUser;
use Wamcar\User\Enum\PersonalOrientationChoices;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;
use Wamcar\User\UserRepository;

class AffinityController extends BaseController
{
    const TYPEFORM_INSTANCE_ID_SESSION_KEY = "_typeform/affinity/instance_id";

    /** @var UserRepository $userRepository */
    private $userRepository;

    /**
     * AffinityController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function personalFormAction()
    {
        $personalUser = $this->getUser();
        if (!$personalUser instanceof PersonalUser) {
            throw $this->createAccessDeniedException();
        }
        $askProject = PersonalOrientationChoices::PERSONAL_ORIENTATION_BOTH()->equals($personalUser->getOrientation())
            || PersonalOrientationChoices::PERSONAL_ORIENTATION_BUY()->equals($personalUser->getOrientation());
        $personalFormInstanceId = uniqid("pf_");
        $this->session->set(self::TYPEFORM_INSTANCE_ID_SESSION_KEY, $personalFormInstanceId);
        $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.affinity.loading_form');
        return $this->render('front/Affinity/personal_form.html.twig', [
            'askProject' => $askProject,
            'instanceId' => $personalFormInstanceId
        ]);
    }

    public function personalFormSubmitedAction()
    {
        $personalUser = $this->getUser();
        if (!$personalUser instanceof PersonalUser) {
            throw $this->createAccessDeniedException();
        }

        // Check if the answer was received
        $this->waitUntilAnswerReceived($personalUser);

        if ($this->session->has(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY)) {
            $this->session->remove(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY);
            // During registration assitant process
            if (PersonalOrientationChoices::PERSONAL_ORIENTATION_BOTH()->equals($personalUser->getOrientation())
                || PersonalOrientationChoices::PERSONAL_ORIENTATION_BUY()->equals($personalUser->getOrientation())) {
                // Validation et compl??tion du projet
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.registration.personal.assitant.process_validation');
                return $this->redirectToRoute('front_edit_user_project');
            } else {
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.registration.personal.assitant.process_end');
                return $this->redirectToRoute('front_view_current_user_info');
            }
        }

        if (PersonalOrientationChoices::PERSONAL_ORIENTATION_BOTH()->equals($personalUser->getOrientation())
            || PersonalOrientationChoices::PERSONAL_ORIENTATION_BUY()->equals($personalUser->getOrientation())) {
            // Validation et compl??tion du projet
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.affinity.form_submited.with_project');
            return $this->redirectToRoute('front_edit_user_project');
        } else {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.affinity.form_submited.without_project');
            return $this->redirectToRoute('front_view_current_user_info');
        }
    }


    public function proFormAction()
    {
        if (!$this->getUser() instanceof ProUser) {
            throw $this->createAccessDeniedException();
        }
        $proFormInstanceId = uniqid("prf_");
        $this->session->set(self::TYPEFORM_INSTANCE_ID_SESSION_KEY, $proFormInstanceId);
        $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.affinity.loading_form');
        return $this->render('front/Affinity/pro_form.html.twig',['instanceId' => $proFormInstanceId]);
    }

    public function proFormSubmitedAction()
    {
        if (!$this->getUser() instanceof ProUser) {
            throw $this->createAccessDeniedException();
        }
        // Check if the answer was received
        $this->waitUntilAnswerReceived($this->getUser());
        // Validation et compl??tion du profil
        $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.registration.pro.validation');
        return $this->redirectToRoute('front_edit_user_info');
    }

    /**
     * Wait until the answer is received throught the webhook. Maximum waiting time is 55s with 10 tries.
     * @param BaseUser $user
     */
    private function waitUntilAnswerReceived(BaseUser $user)
    {
        if ($this->session->has(self::TYPEFORM_INSTANCE_ID_SESSION_KEY)) {
            $userReloaded = $this->userRepository->findOne($user->getId());
            $countSleep = 0;
            while ($countSleep < 10 && ($userReloaded->getAffinityAnswer() == null
                    || $userReloaded->getAffinityAnswer()->getInstanceId() !== $this->session->get(self::TYPEFORM_INSTANCE_ID_SESSION_KEY))) {
                // Sleeping time increase with number of tries. Max total waiting time 10s
                sleep(1);
                $countSleep++;
                $userReloaded = $this->userRepository->findOne($user->getId());
            }
            $this->session->remove(self::TYPEFORM_INSTANCE_ID_SESSION_KEY);
        }
    }
}