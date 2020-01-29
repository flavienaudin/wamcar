<?php

namespace AppBundle\Controller\Front;

use AppBundle\Controller\Front\PersonalContext\RegistrationController;
use AppBundle\Doctrine\Entity\FooterLink;
use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Doctrine\Repository\FooterLinkRepository;
use AppBundle\Elasticsearch\Elastica\VehicleInfoEntityIndexer;
use AppBundle\Form\DTO\SearchVehicleDTO;
use AppBundle\Form\DTO\VehicleInformationDTO;
use AppBundle\Form\Type\PersonalRegistrationOrientationType;
use AppBundle\Form\Type\SearchVehicleType;
use AppBundle\Form\Type\VehicleInformationType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Wamcar\User\Enum\PersonalOrientationChoices;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\ProVehicleRepository;

class DefaultController extends BaseController
{
    /** Number of pro vehicles to display in the homepage */
    const NB_PRO_VEHICLE_IN_HOMEPAGE = 6;

    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var VehicleInfoEntityIndexer */
    private $vehicleInfoEntityIndexer;
    /** @var ProVehicleRepository $proVehicleRepository */
    private $proVehicleRepository;
    /** @var DoctrineProUserRepository */
    private $proUserRepository;
    /** @var FooterLinkRepository */
    private $footerLinkRepository;

    /**
     * DefaultController constructor.
     * @param FormFactoryInterface $formFactory
     * @param VehicleInfoEntityIndexer $vehicleInfoEntityIndexer
     * @param DoctrineProUserRepository $proUserRepository
     * @param FooterLinkRepository $footerLinkRepository
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        VehicleInfoEntityIndexer $vehicleInfoEntityIndexer,
        ProVehicleRepository $proVehicleRepository,
        DoctrineProUserRepository $proUserRepository,
        FooterLinkRepository $footerLinkRepository
    )
    {
        $this->formFactory = $formFactory;
        $this->vehicleInfoEntityIndexer = $vehicleInfoEntityIndexer;
        $this->proVehicleRepository = $proVehicleRepository;
        $this->proUserRepository = $proUserRepository;
        $this->footerLinkRepository = $footerLinkRepository;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function footerAction(Request $request): Response
    {
        $footerLinks = $this->footerLinkRepository->findAllOrdered();
        $footerLinksByColumn = [];
        /** @var FooterLink $footerLink */
        foreach ($footerLinks as $footerLink) {
            if (!isset($footerLinksByColumn[$footerLink->getColumnNumber()])) {
                $footerLinksByColumn[$footerLink->getColumnNumber()] = [];
            }
            $footerLinksByColumn[$footerLink->getColumnNumber()][$footerLink->getPosition()] = $footerLink;
        }

        return $this->render('front/Layout/includes/footer.html.twig', [
            'isLogged' => $this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED),
            'isUserPro' => $this->getUser() != null && $this->getUser() instanceof ProUser,
            'isUserPersonal' => $this->getUser() != null && $this->getUser() instanceof PersonalUser,
            'footerLinksByColumn' => $footerLinksByColumn
        ]);
    }

    /**
     * Landing /reprise
     * @return Response
     */
    public function landingRepriseAction(): Response
    {
        $vehicleInformationForm = $this->formFactory->create(
            VehicleInformationType::class,
            new VehicleInformationDTO(),
            [
                'available_values' => $this->vehicleInfoEntityIndexer->getVehicleInfoAggregates(),
                'small_version' => true
            ]
        );

        $searchVehicleForm = $this->formFactory->create(
            SearchVehicleType::class,
            new SearchVehicleDTO(),
            [
                'action' => $this->generateRoute('front_search'),
                'small_version' => true
            ]
        );

        $last_vehicles = $this->proVehicleRepository->getLast(self::NB_PRO_VEHICLE_IN_HOMEPAGE);

        return $this->render(
            ':front/Home:home.html.twig',
            [
                'vehicleInformationForm' => $vehicleInformationForm->createView(),
                'smallSearchForm' => $searchVehicleForm->createView(),
                'last_vehicles' => $last_vehicles
            ]
        );
    }

    /**
     * Landing /rencontre
     * @return Response
     */
    public function landingMeetingAction(): Response
    {
        $searchVehicleForm = $this->formFactory->create(
            SearchVehicleType::class,
            new SearchVehicleDTO(),
            [
                'action' => $this->generateRoute('front_search'),
                'small_version' => true
            ]
        );

        $last_vehicles = $this->proVehicleRepository->getLast(self::NB_PRO_VEHICLE_IN_HOMEPAGE);

        return $this->render(
            '/front/Home/landing_meeting.html.twig',
            [
                'smallSearchForm' => $searchVehicleForm->createView(),
                'last_vehicles' => $last_vehicles
            ]
        );
    }

    /**
     * Page d'accueil par défaut : /
     * @param Request $request
     * @return Response
     */
    public function landingMixteAction(Request $request): Response
    {
        $searchVehicleForm = $this->formFactory->create(
            SearchVehicleType::class,
            new SearchVehicleDTO(),
            [
                'action' => $this->generateRoute('front_search'),
                'small_version' => true
            ]
        );

        $proProfils = $this->proUserRepository->findProUsersForHomepage();
        $last_vehicles = $this->proVehicleRepository->getLastWithPicture(self::NB_PRO_VEHICLE_IN_HOMEPAGE);
        $personalOrientationForm = $this->formFactory->create(PersonalRegistrationOrientationType::class);
        $personalOrientationForm->handleRequest($request);
        if ($personalOrientationForm->isSubmitted() && $personalOrientationForm->isValid()) {
            $formData = $personalOrientationForm->getData();
            $this->session->set(RegistrationController::PERSONAL_ORIENTATION_ACTION_SESSION_KEY, $formData['orientation']->getValue());

            if (PersonalOrientationChoices::PERSONAL_ORIENTATION_BUY === $formData['orientation']->getValue()) {
                if ($this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)) {
                    return $this->redirectToRoute('front_search');
                } else {
                    return $this->redirectToRoute('register', ['type' => PersonalUser::TYPE]);
                }
            } else {
                return $this->redirectToRoute('front_vehicle_registration');
            }

        }
        return $this->render(
            '/front/Home/landing_mixte.html.twig',
            [
                'personalOrientationForm' => $personalOrientationForm->createView(),
                'smallSearchForm' => $searchVehicleForm->createView(),
                'last_vehicles' => $last_vehicles,
                'proProfils' => $proProfils
            ]
        );
    }

    /**
     * Page d'accueil par défaut : /
     * @param Request $request
     * @return Response
     */
    public function landingPeexeoAction(Request $request): Response
    {
        $proProfils = $this->proUserRepository->findProUsersForHomepage();

        return $this->render(
            '/front/Home/landing_peexeo.html.twig',
            [
                'proProfils' => $proProfils
            ]
        );
    }
}
