<?php

namespace AppBundle\Controller\Front\ProContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ApplicationGarage;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Doctrine\Repository\DoctrineGarageRepository;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\Type\GarageType;
use AppBundle\Services\Garage\GarageEditionService;
use AppBundle\Services\Garage\GarageProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Wamcar\Garage\Garage;
use Symfony\Component\HttpFoundation\Response;

class GarageController extends BaseController
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var DoctrineGarageRepository  */
    protected $doctrineGarageRepository;

    /** @var GarageEditionService  */
    protected $garageEditionService;

    /** @var GarageProvider  */
    protected $garageProvider;
    /**
     * SecurityController constructor.
     * @param FormFactoryInterface $formFactory
     * @param DoctrineGarageRepository $doctrineGarageRepository
     * @param GarageEditionService $garageEditionService
     * @param GarageProvider $garageProvider
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        DoctrineGarageRepository $doctrineGarageRepository,
        GarageEditionService $garageEditionService,
        GarageProvider $garageProvider
    )
    {
        $this->formFactory = $formFactory;
        $this->doctrineGarageRepository = $doctrineGarageRepository;
        $this->doctrineUserRepository = $doctrineUserRepository;
        $this->garageEditionService = $garageEditionService;
        $this->garageProvider = $garageProvider;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $lastGarages = $this->garageProvider->provideLatest();

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
            'garage' => $garage
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, ?Garage $garage )
    {
        $garageDTO = new GarageDTO($garage);
        $garageForm = $this->formFactory->create(GarageType::class, $garageDTO);
        $garageForm->handleRequest($request);

        if ($garageForm->isSubmitted() && $garageForm->isValid()) {
            $this->garageEditionService->editInformations($garageDTO, $garage);

            $this->session->getFlashBag()->add(
                'flash.success.garage_create',
                self::FLASH_LEVEL_INFO
            );
            return $this->redirectToRoute('front_garage_list');
        }

        return $this->render('front/Garages/Edit/edit.html.twig', [
            'garageForm' => $garageForm->createView()
        ]);
    }

    /**
     * @param ApplicationGarage $applicationGarage
     * @Security("has_role('ROLE_ADMIN')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(ApplicationGarage $applicationGarage): RedirectResponse
    {
        $this->doctrineGarageRepository->remove($applicationGarage);

        $this->session->getFlashBag()->add(
            'flash.success.remove_garage',
            self::FLASH_LEVEL_INFO
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
            'flash.success.add_member_garage',
            self::FLASH_LEVEL_INFO
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
            'flash.success.add_member_garage',
            self::FLASH_LEVEL_INFO
        );

        return $this->redirectToRoute('front_garage_list');
    }
}
