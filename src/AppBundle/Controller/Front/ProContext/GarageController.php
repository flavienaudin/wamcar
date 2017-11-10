<?php

namespace AppBundle\Controller\Front\ProContext;

use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ApplicationGarage;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\Type\GarageType;
use AppBundle\Services\Garage\GarageEditionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Wamcar\Garage\Garage;
use Symfony\Component\HttpFoundation\Response;
use Wamcar\Garage\GarageRepository;

class GarageController extends BaseController
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var GarageRepository  */
    protected $garageRepository;

    /** @var GarageEditionService  */
    protected $garageEditionService;

    /**
     * GarageController constructor.
     * @param FormFactoryInterface $formFactory
     * @param GarageRepository $garageRepository
     * @param GarageEditionService $garageEditionService
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        GarageRepository $garageRepository,
        GarageEditionService $garageEditionService
    )
    {
        $this->formFactory = $formFactory;
        $this->garageRepository = $garageRepository;
        $this->garageEditionService = $garageEditionService;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        if(!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException('Only admin can access garage listing');
        }

        $lastGarages = $this->garageRepository->getLatest();

        return $this->render('front/proContext/garage/garage_list.html.twig', [
            'garages' => $lastGarages
        ]);
    }

    /**
     * @param Request $request
     * @param Garage $garage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, Garage $garage)
    {
        return $this->render('front/Garages/Detail/detail.html.twig', [
            'isEditableByCurrentUser' => $this->garageEditionService->canEdit($this->getUser(), $garage),
            'garage' => $garage
        ]);
    }

    /**
     * @param Request $request
     * @param null|Garage $garage
     * @return RedirectResponse|Response
     */
    public function saveAction(Request $request, ?Garage $garage)
    {
        $garageDTO = new GarageDTO($garage);
        $garageForm = $this->formFactory->create(GarageType::class, $garageDTO);
        $garageForm->handleRequest($request);

        if ($garageForm->isSubmitted() && $garageForm->isValid()) {
            $successMessage = null === $garage ? 'flash.success.garage_create' : 'flash.success.garage_edit' ;
            $garage = $this->garageEditionService->editInformations($garageDTO, $garage, $this->getUser());

            $this->session->getFlashBag()->add(
                self::FLASH_LEVEL_INFO,
                $successMessage
            );
            return $this->redirectToRoute('front_garage_view', ['id' => $garage->getId()]);
        }

        return $this->render('front/Garages/Edit/edit.html.twig', [
            'isNew' => $garageDTO->isNew,
            'garageForm' => $garageForm->createView(),
        ]);
    }

    /**
     * @param Garage $garage
     * @Security("has_role('ROLE_ADMIN')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Garage $garage): RedirectResponse
    {
        $this->garageRepository->remove($garage);

        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_INFO,
            'flash.success.remove_garage'
        );

        return $this->redirectToRoute('front_garage_list');
    }

    /**
     * @param Garage $garage
     * @Security("has_role('ROLE_PRO')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function assignAction(Garage $garage): RedirectResponse
    {
        $user = $this->getUser();

        $this->garageEditionService->addMember($garage, $user);

        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_INFO,
            'flash.success.add_member_garage'
        );

        return $this->redirectToRoute('front_garage_list');
    }

    /**
     * @ParamConverter("garage", options={"id" = "garage_id"})
     * @ParamConverter("user", options={"id" = "user_id"})
     * @param Garage $garage
     * @param ProApplicationUser $user
     * @Security("has_role('ROLE_PRO')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unassignAction(Garage $garage, ProApplicationUser $user): RedirectResponse
    {
        $this->garageEditionService->removeMember($garage, $user);

        $this->session->getFlashBag()->add(
            self::FLASH_LEVEL_INFO,
            'flash.success.add_member_garage'
        );

        return $this->redirectToRoute('front_garage_list');
    }
}
