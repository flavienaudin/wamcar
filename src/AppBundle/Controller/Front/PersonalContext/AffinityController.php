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
        if (!$this->getUser() instanceof PersonalUser) {
            throw $this->createAccessDeniedException();
        }
        $actionChoice = $this->session->get(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY);
        $askProject = ($actionChoice === PersonalOrientationChoices::PERSONAL_ORIENTATION_BOTH
            || $actionChoice == PersonalOrientationChoices::PERSONAL_ORIENTATION_BUY);
        return $this->render('front/Affinity/personal_form.html.twig', [
            'askProject' => $askProject
        ]);
    }

    public function personalFormSubmitedAction()
    {
        if (!$this->getUser() instanceof PersonalUser) {
            throw $this->createAccessDeniedException();
        }

        if ($this->session->has(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY)) {
            $actionChoice = $this->session->get(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY);
            if ($actionChoice === PersonalOrientationChoices::PERSONAL_ORIENTATION_BOTH ||
                $actionChoice === PersonalOrientationChoices::PERSONAL_ORIENTATION_BUY) {
                // Validation et complétion du projet
                $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.registration.personal.assitant.process_validation');
                return $this->redirectToRoute('front_edit_user_project');
            }
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.registration.personal.assitant.process_end');
        } else {
            $this->session->getFlashBag()->add(self::FLASH_LEVEL_INFO, 'flash.success.affinity.form_submited');
        }
        return $this->redirectToRoute('front_view_current_user_info');
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