<?php

namespace AppBundle\Services\Vehicle;


use AppBundle\Services\Picture\PathVehiclePicture;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Wamcar\User\Project;
use Wamcar\User\ProjectRepository;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\PersonalVehicleRepository;

class VehicleExportService
{
    const DEST_POLEVO = "polevo";

    /** @var ProjectRepository */
    private $projectRepository;
    /** @var PersonalVehicleRepository */
    private $personalVehicleRepository;
    /** @var PathVehiclePicture */
    private $pathVehiclePicture;
    /** @var RouterInterface */
    private $router;
    /** @var string */
    private $contact_name;
    /** @var string */
    private $contact_email;
    /** @var string */
    private $contact_tel;
    /** @var string */
    private $contact_address;
    /** @var string */
    private $contact_city;
    /** @var string */
    private $contact_zip;

    /**
     * VehicleExportService constructor.
     * @param ProjectRepository $projectRepository
     * @param PersonalVehicleRepository $personalVehicleRepository
     * @param PathVehiclePicture $pathVehiclePicture
     * @param RouterInterface $router
     * @param string $contact_name
     * @param string $contact_email
     * @param string $contact_tel
     * @param string $contact_address
     * @param string $contact_city
     * @param string $contact_zip
     */
    public function __construct(ProjectRepository $projectRepository, PersonalVehicleRepository $personalVehicleRepository, PathVehiclePicture $pathVehiclePicture, RouterInterface $router, string $contact_name, string $contact_email, string $contact_tel, string $contact_address, string $contact_city, string $contact_zip)
    {
        $this->projectRepository = $projectRepository;
        $this->personalVehicleRepository = $personalVehicleRepository;
        $this->pathVehiclePicture = $pathVehiclePicture;
        $this->router = $router;
        $this->contact_name = $contact_name;
        $this->contact_email = $contact_email;
        $this->contact_tel = $contact_tel;
        $this->contact_address = $contact_address;
        $this->contact_city = $contact_city;
        $this->contact_zip = $contact_zip;
    }

    /**
     * Export personal vehicles and personal project to XML files
     * @return array Numbers of personal projects and vehicles exported
     */
    public function exportVehiclesToPoleVO()
    {
        $exportXMLFilesDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'polevo' . DIRECTORY_SEPARATOR;
        if (file_exists($exportXMLFilesDir) === FALSE) {
            mkdir($exportXMLFilesDir);
        }

        // Demandes des particuliers
        $projectsToExport = $this->projectRepository->findAll();
        $demandesXml = new \SimpleXMLElement('<demandes></demandes>');
        $demandesXml->addAttribute("date", date(\DateTime::ISO8601));
        // Contact Wamcar
        $contactElt = $demandesXml->addChild('contact');
        $contactElt->addChild('name', $this->contact_name);
        $contactElt->addChild('email', $this->contact_email);
        $contactElt->addChild('tel', strval($this->contact_tel));
        $contactElt->addChild('address', $this->contact_address);
        $contactElt->addChild('zip', $this->contact_zip);
        $contactElt->addChild('city', $this->contact_city);
        /** @var Project $project */
        foreach ($projectsToExport as $project) {
            $demandeElt = $demandesXml->addChild('demande');
            $demandeElt->addAttribute('id', $project->getId());
            $demandeElt->addAttribute('personal_id', $project->getPersonalUser()->getId());
            $demandeElt->addChild('project_url', $this->router->generate('front_view_personal_user_info', [
                'slug' => $project->getPersonalUser()->getSlug()
            ], UrlGeneratorInterface::ABSOLUTE_URL));
            $demandeElt->addChild('search_fleet', $project->isFleet());
            $demandeElt->addChild('budget', $project->getBudget());
            $demandeElt->addChild('description', htmlspecialchars($project->getDescription()));
            $demandeElt->addChild('createdAt', $project->getCreatedAt()->format(\DateTime::ISO8601));
            foreach ($project->getProjectVehicles() as $projectVehicle) {
                $modelChild = $demandeElt->addChild('searched_model');
                $modelChild->addChild('make', $projectVehicle->getMake());
                $modelChild->addChild('model', $projectVehicle->getModel());
                $modelChild->addChild('year_min', $projectVehicle->getYearMin());
                $modelChild->addChild('mileage_max', $projectVehicle->getMileageMax());
            }
        }

        $demandesXMLFileName = date('Ymd') . '_demandes_particulier.xml';
        $demandesXml->asXML($exportXMLFilesDir . $demandesXMLFileName);


        // Reprises des particuliers
        $vehicesToExport = $this->personalVehicleRepository->findAll();

        $reprisesXml = new \SimpleXMLElement('<reprises></reprises>');
        $reprisesXml->addAttribute("date", date(\DateTime::ISO8601));
        // Contact Wamcar
        $contactElt = $reprisesXml->addChild('contact');
        $contactElt->addChild('name', $this->contact_name);
        $contactElt->addChild('email', $this->contact_email);
        $contactElt->addChild('tel', strval($this->contact_tel));
        $contactElt->addChild('address', $this->contact_address);
        $contactElt->addChild('zip', $this->contact_zip);
        $contactElt->addChild('city', $this->contact_city);
        /** @var PersonalVehicle $vehicle */
        foreach ($vehicesToExport as $vehicle) {
            $repriseElt = $reprisesXml->addChild('reprise');
            $repriseElt->addAttribute('id', $vehicle->getId());
            $repriseElt->addChild('webpage', $this->router->generate('front_vehicle_personal_detail', [
                'slug' => $vehicle->getSlug()
            ], UrlGeneratorInterface::ABSOLUTE_URL));
            $repriseElt->addChild('name', $vehicle->getFullName());
            $repriseElt->addChild('fuel', $vehicle->getFuelName());
            $repriseElt->addChild('gear_box', $vehicle->getTransmission());
            $repriseElt->addChild('release', $vehicle->getRegistrationDate()->format('Y-m-d'));
            $repriseElt->addChild('mileage', $vehicle->getMileage());
            $repriseElt->addChild('make', $vehicle->getMake());
            $repriseElt->addChild('model', $vehicle->getModelName());
            $repriseElt->addChild('created_at', $vehicle->getCreatedAt()->format(\DateTime::ISO8601));
            $repriseElt->addChild('details', htmlspecialchars($vehicle->getAdditionalInformation()));
            $photos = $repriseElt->addChild('pictures');
            if (count($vehicle->getPictures()) > 0) {
                foreach ($vehicle->getPictures() as $picture) {
                    $photos->addChild('picture', $this->pathVehiclePicture->getPath($picture, 'vehicle_picture'));
                }
            } else {
                $photos->addChild('picture', $this->pathVehiclePicture->getPath(null, 'vehicle_placeholder_thumbnail'));
            }
        }

        $reprisesXMLFileName = date('Ymd') . '_reprises_particulier.xml';
        $reprisesXml->asXML($exportXMLFilesDir . $reprisesXMLFileName);

        return [
            'demandes' => [
                'count' => count($projectsToExport),
                'fileDir' => $exportXMLFilesDir,
                'fileName' => $demandesXMLFileName
            ],
            'reprises' => [
                'count' => count($vehicesToExport),
                'fileDir' => $exportXMLFilesDir,
                'fileName' => $reprisesXMLFileName
            ]];
    }
}