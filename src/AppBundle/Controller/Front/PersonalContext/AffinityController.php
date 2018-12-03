<?php

namespace AppBundle\Controller\Front\PersonalContext;


use AppBundle\Controller\Front\BaseController;
use Wamcar\User\Enum\PersonalOrientationChoices;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;

class AffinityController extends BaseController
{

    public function personalFormAction()
    {
        $personalUser = $this->getUser();
        if (!$personalUser instanceof PersonalUser) {
            throw $this->createAccessDeniedException();
        }
        $askProject = PersonalOrientationChoices::PERSONAL_ORIENTATION_BOTH()->equals($personalUser->getOrientation())
            || PersonalOrientationChoices::PERSONAL_ORIENTATION_BUY()->equals($personalUser->getOrientation());
        return $this->render('front/Affinity/personal_form.html.twig', [
            'askProject' => $askProject
        ]);
    }

    public function personalFormSubmitedAction()
    {
        $personalUser = $this->getUser();
        if (!$personalUser instanceof PersonalUser) {
            throw $this->createAccessDeniedException();
        }

        if ($this->session->has(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY)) {
            // During registration assitant process
            if (PersonalOrientationChoices::PERSONAL_ORIENTATION_BOTH()->equals($personalUser->getOrientation())
                || PersonalOrientationChoices::PERSONAL_ORIENTATION_BUY()->equals($personalUser->getOrientation())) {
                // Validation et complétion du projet
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.registration.personal.assitant.process_validation');
                return $this->redirectToRoute('front_edit_user_project');
            } else {
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.registration.personal.assitant.process_end');
                $this->session->remove(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY);
                return $this->redirectToRoute('front_view_current_user_info');
            }
        }

        if (PersonalOrientationChoices::PERSONAL_ORIENTATION_BOTH()->equals($personalUser->getOrientation())
            || PersonalOrientationChoices::PERSONAL_ORIENTATION_BUY()->equals($personalUser->getOrientation())) {
            // Validation et complétion du projet
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
        return $this->render('front/Affinity/pro_form.html.twig');
    }

    public function proFormSubmitedAction()
    {
        if (!$this->getUser() instanceof ProUser) {
            throw $this->createAccessDeniedException();
        }

        // Validation et complétion du profil
        $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.registration.pro.validation');
        return $this->redirectToRoute('front_edit_user_info');
    }

}