<?php

namespace AppBundle\Controller\Front\ProContext;


use AppBundle\Controller\Front\BaseController;
use AppBundle\Doctrine\Entity\ApplicationGarage;
use AppBundle\Doctrine\Repository\DoctrineGarageRepository;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Form\Type\GarageType;
use AppBundle\Services\Garage\GarageEditionService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class GarageController extends BaseController
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var DoctrineGarageRepository  */
    protected $doctrineGarageRepository;

    /** @var GarageEditionService  */
    protected $garageEditionService;

    /**
     * SecurityController constructor.
     * @param FormFactoryInterface $formFactory
     * @param DoctrineGarageRepository $doctrineGarageRepository
     * @param GarageEditionService $garageEditionService
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        DoctrineGarageRepository $doctrineGarageRepository,
        GarageEditionService $garageEditionService
    )
    {
        $this->formFactory = $formFactory;
        $this->doctrineGarageRepository = $doctrineGarageRepository;
        $this->garageEditionService = $garageEditionService;
    }

    /**
     * @param Request $request
     * @param null|ApplicationGarage $applicationGarage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, ?ApplicationGarage $applicationGarage )
    {
        $garageDTO = new GarageDTO($applicationGarage);
        $garageForm = $this->formFactory->create(GarageType::class, $garageDTO);

        $garageForm->handleRequest($request);

        if ($garageForm->isSubmitted() && $garageForm->isValid()) {
            $this->garageEditionService->editInformations($garageDTO, $applicationGarage);

            $this->session->getFlashBag()->add(
                'flash.success.garage_edit',
                self::FLASH_LEVEL_INFO
            );
        }

        return $this->render('front/Garages/Edit/edit.html.twig', [
            'garageForm' => $garageForm->createView()
        ]);
    }
}
