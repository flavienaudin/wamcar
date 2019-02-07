<?php

namespace AppBundle\Controller\Front\AdministrationContext;


use AppBundle\Services\Garage\GarageEditionService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\Garage\Event\GarageUpdated;
use Wamcar\Garage\Garage;
use Wamcar\User\BaseUser;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;

class BackendController extends AdminController
{

    /** @var GarageEditionService */
    private $garageEditionService;

    /** @var MessageBus */
    private $eventBus;

    /**
     * @param GarageEditionService $garageEditionService
     */
    public function setGarageEditionService(GarageEditionService $garageEditionService): void
    {
        $this->garageEditionService = $garageEditionService;
    }

    /**
     * @param MessageBus $eventBus
     */
    public function setEventBus(MessageBus $eventBus): void
    {
        $this->eventBus = $eventBus;
    }

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

    protected function removeEntity($entity)
    {
        if ($entity instanceof Garage) {
            $this->garageEditionService->remove($entity);
        } else {
            // TODO other entities
            //parent::removeEntity($entity);
            return;
        }
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
}