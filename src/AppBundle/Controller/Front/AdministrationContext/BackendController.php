<?php

namespace AppBundle\Controller\Front\AdministrationContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Security\Voter\UserVoter;
use AppBundle\Services\Garage\GarageEditionService;
use AppBundle\Services\User\HobbyService;
use AppBundle\Services\User\ProServiceService;
use AppBundle\Services\User\UserEditionService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Garage\Garage;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\ProUserUpdated;
use Wamcar\User\Hobby;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProService;
use Wamcar\User\ProServiceCategory;
use Wamcar\User\ProUser;
use Wamcar\User\ProUserProService;

/**
 * Note umportante: EasyAdminSubscriber permet de :
 * - disableSoftDeletableFilter : pour le listing et la recherche d'entité
 * - Lancer les événements (via eventBus) xUpdated sur les entités lors des événements post_persist et post_update
 *
 * Class BackendController
 * @package AppBundle\Controller\Front\AdministrationContext
 */
class BackendController extends AdminController
{
    /** @var TranslatorInterface */
    private $translator;
    /** @var MessageBus */
    private $eventBus;
    /** @var GarageEditionService */
    private $garageEditionService;
    /** @var UserEditionService */
    private $userEditionService;
    /** @var ProServiceService */
    private $proServiceService;
    /** @var HobbyService */
    private $hobbyService;

    /** @param TranslatorInterface $translator */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    /** @param MessageBus $eventBus */
    public function setEventBus(MessageBus $eventBus): void
    {
        $this->eventBus = $eventBus;
    }

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

    /** @param ProServiceService $proServiceService */
    public function setProServiceService(ProServiceService $proServiceService): void
    {
        $this->proServiceService = $proServiceService;
    }

    /** @param HobbyService $hobbyService */
    public function setHobbyService(HobbyService $hobbyService): void
    {
        $this->hobbyService = $hobbyService;
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
            if (!$this->isGranted(UserVoter::DELETE, $entity)) {
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
        } elseif ($entity instanceof ProUserProService) {
            $serviceName = $entity->getProService()->getName();
            $userName = $entity->getProUser()->getFullName();
            $this->userEditionService->deleteProUserProService($entity);
            $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO,
                $this->translator->trans('flash.success.pro_user_pro_service.delete', [
                        '%servicename%' => $serviceName,
                        '%username%' => $userName
                    ]
                ));
        } elseif ($entity instanceof ProService) {
            $serviceName = $entity->getName();

            // ES Update of ProUser with this service
            $proUSerToUpdate = [];
            array_map(function (ProUserProService $proUserProService) use (&$proUSerToUpdate) {
                $proUSerToUpdate[] = $proUserProService->getProUser();
            }, $entity->getProUserProServices()->toArray());
            $this->proServiceService->deleteProService($entity);
            foreach ($proUSerToUpdate as $proUser) {
                $this->eventBus->handle(new ProUserUpdated($proUser));
            }
            $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO,
                $this->translator->trans('flash.success.pro_service.delete', ['%servicename%' => $serviceName]));
        } elseif ($entity instanceof ProServiceCategory) {
            $categoryName = $entity->getLabel();

            $proUSerToUpdate = [];
            /** @var ProService $proService */
            foreach ($entity->getProServices() as $proService) {
                // ES Update of ProUser with this service
                array_map(function (ProUserProService $proUserProService) use (&$proUSerToUpdate) {
                    $proUSerToUpdate[] = $proUserProService->getProUser();
                }, $proService->getProUserProServices()->toArray());
            }
            $this->proServiceService->deleteProServiceCategory($entity);
            foreach ($proUSerToUpdate as $proUser) {
                $this->eventBus->handle(new ProUserUpdated($proUser));
            }
            $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO,
                $this->translator->trans('flash.success.pro_service_category.delete', ['%servicecategory%' => $categoryName]));
        } elseif ($entity instanceof Hobby) {
            $hobbyName = $entity->getName();
            $this->hobbyService->deleteHobby($entity);
            $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_INFO,
                $this->translator->trans('flash.success.hobby.delete', ['%hobbyName%' => $hobbyName]));

        } else {
            // TODO other entities
            // parent::removeEntity($entity);
            $this->get('session')->getFlashBag()->add(BaseController::FLASH_LEVEL_WARNING,
                'Suppression pas encore configurée');
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