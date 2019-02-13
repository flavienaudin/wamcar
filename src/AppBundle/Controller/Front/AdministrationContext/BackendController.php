<?php

namespace AppBundle\Controller\Front\AdministrationContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Services\Garage\GarageEditionService;
use AppBundle\Services\User\UserEditionService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\Garage\Event\GarageUpdated;
use Wamcar\Garage\Garage;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\PersonalProjectUpdated;
use Wamcar\User\Event\PersonalUserUpdated;
use Wamcar\User\Event\ProUserUpdated;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;

class BackendController extends AdminController
{
    /** @var GarageEditionService */
    private $garageEditionService;
    /** @var UserEditionService */
    private $userEditionService;
    /** @var MessageBus */
    private $eventBus;

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

    /** @param MessageBus $eventBus */
    public function setEventBus(MessageBus $eventBus): void
    {
        $this->eventBus = $eventBus;
    }

    // Persit/Update entity

    // ProApplicationUser
    public function persistProApplicationUserEntity($entity)
    {
        parent::persistEntity($entity);
        $this->eventBus->handle(new ProUserUpdated($entity));
    }

    public function updateProApplicationUserEntity($entity)
    {
        parent::updateEntity($entity);
        $this->eventBus->handle(new ProUserUpdated($entity));
    }

    // PersonalApplicationUser
    public function persistPersonalApplicationUserEntity($entity)
    {
        parent::persistEntity($entity);
        $this->eventBus->handle(new PersonalUserUpdated($entity));
        if ($entity->getProject() != null) {
            $this->eventBus->handle(new PersonalProjectUpdated($entity->getProject()));
        }
    }

    public function updatePersonalApplicationUserEntity($entity)
    {
        parent::updateEntity($entity);
        $this->eventBus->handle(new PersonalUserUpdated($entity));
        if ($entity->getProject() != null) {
            $this->eventBus->handle(new PersonalProjectUpdated($entity->getProject()));
        }
    }

    // Garage
    public function persistGarageEntity($entity)
    {
        parent::persistEntity($entity);
        $this->eventBus->handle(new GarageUpdated($entity));
    }

    public function updateGarageEntity($entity)
    {
        parent::updateEntity($entity);
        $this->eventBus->handle(new GarageUpdated($entity));
    }

    // Common
    protected function removeEntity($entity)
    {
        $isAlreadySoftDeleted = $entity->getDeletedAt() != null;
        if ($entity instanceof Garage) {
            try {
                $this->garageEditionService->remove($entity);
                if ($isAlreadySoftDeleted) {
                    $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO, 'flash.success.garage.deleted.hard');
                } else {
                    if ($isAlreadySoftDeleted) {
                        $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO, 'flash.success.garage.deleted.soft');
                    }
                }
            } catch (\InvalidArgumentException $exception) {
                $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_WARNING,$exception->getMessage());
            }
        } elseif ($entity instanceof BaseUser) {
            $resultMessages = $this->userEditionService->deleteUser($entity, $this->getUser());
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
     * Action to see garage page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewGaragePageAction()
    {
        $id = $this->request->query->get('id');
        $entity = $this->em->getRepository(Garage::class)->find($id);
        return $this->redirectToRoute('front_garage_view', [
            'slug' => $entity->getSlug()
        ]);
    }
}