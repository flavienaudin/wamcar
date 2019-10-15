<?php

namespace AppBundle\Controller\Front\AdministrationContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Security\Voter\UserVoter;
use AppBundle\Services\Garage\GarageEditionService;
use AppBundle\Services\User\UserEditionService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Wamcar\Garage\Garage;
use Wamcar\User\BaseUser;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;

class BackendController extends AdminController
{
    /** @var GarageEditionService */
    private $garageEditionService;
    /** @var UserEditionService */
    private $userEditionService;

    /** @param GarageEditionService $garageEditionService */
    public function setGarageEditionService(GarageEditionService $garageEditionService): void
    {
        $this->garageEditionService = $garageEditionService;
    }

    /** @param UserEditionService $userEditionService */
    public function setUserEditionService(UserEditionService $userEditionService): void
    {
        $this->userEditionService = $userEditionService;
    }

    // Common
    protected function removeEntity($entity)
    {
        if ($entity instanceof Garage) {
            $isAlreadySoftDeleted = $entity->getDeletedAt() != null;
            try {
                $this->garageEditionService->remove($entity);
                if ($isAlreadySoftDeleted) {
                    $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO, 'flash.success.garage.deleted.hard');
                } else {
                    $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO, 'flash.success.garage.deleted.soft');
                }
            } catch (\InvalidArgumentException $exception) {
                $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_WARNING, $exception->getMessage());
            }
        } elseif ($entity instanceof BaseUser) {
            $isAlreadySoftDeleted = $entity->getDeletedAt() != null;
            if(!$this->isGranted(UserVoter::DELETE, $entity)){
                $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_WARNING, 'flash.error.user.deletion_not_allowed');
                return;
            }
            $resultMessages = $this->userEditionService->deleteUser($entity, $this->getUser(), 'Utilisateur supprimé par un administrateur');
            foreach ($resultMessages['errorMessages'] as $errorMessage) {
                $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_WARNING, $errorMessage);
            }
            foreach ($resultMessages['successMessages'] as $garageId => $successMessage) {
                $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO,
                    'Garage (' . $garageId . ') : ' . $this->get('translator')->trans($successMessage));
            }
            if (count($resultMessages['errorMessages']) == 0) {
                if ($isAlreadySoftDeleted) {
                    $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO, 'flash.success.user.deleted.hard');
                } else {
                    $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO, 'flash.success.user.deleted.soft');
                }
            }
        } else {
            // TODO other entities
            // parent::removeEntity($entity);
            return;
        }
    }

    // CUSTOM ACTIONS

    /**
     * Action to edit user profile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editUserProfileAction()
    {
        $id = $this->request->query->get('id');
        $entity = $this->em->getRepository(BaseUser::class)->find($id);
        if ($entity instanceof ProUser) {
            return $this->redirectToRoute('admin_pro_user_edit', [
                'slug' => $entity->getSlug()
            ]);
        } elseif ($entity instanceof PersonalUser) {
            $this->addFlash('error', 'Impossible d\'éditer un PersonalUser ');
        }
        // redirect to the 'list' view of the given entity ...
        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ));
    }

    /**
     * Action to see user profile
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewUserProfileAction()
    {
        $id = $this->request->query->get('id');
        $entity = $this->em->getRepository(BaseUser::class)->find($id);
        if ($entity instanceof ProUser) {
            return $this->redirectToRoute('front_view_pro_user_info', [
                'slug' => $entity->getSlug()
            ]);
        } elseif ($entity instanceof PersonalUser) {
            return $this->redirectToRoute('front_view_personal_user_info', [
                'slug' => $entity->getSlug()
            ]);
        }
        // redirect to the 'list' view of the given entity ...
        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ));
    }

    /**
     * Action to convert personal account to pro account
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function convertToProAccountAction()
    {
        $id = $this->request->query->get('id');
        /** @var PersonalUser $entity */
        $entity = $this->em->getRepository(PersonalUser::class)->find($id);
        $this->userEditionService->convertPersonalToProUser($entity, $this->getUser());


        // redirect to the 'list' view of the given entity ...
        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => 'ProApplicationUser'
        ));
    }

    /**
     * Action to see garage page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewGaragePageAction()
    {
        $id = $this->request->query->get('id');
        $entity = $this->em->getRepository(Garage::class)->find($id);
        return $this->redirectToRoute('front_garage_view', ['slug' => $entity->getSlug()]);
    }

    /**
     * Action to see pro user performances
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewProUserPerformancesAction()
    {
        $id = $this->request->query->get('id');
        $entity = $this->em->getRepository(ProUser::class)->find($id);
        return $this->redirectToRoute('front_pro_seller_performances', ['slug' => $entity->getSlug()]);
    }
}