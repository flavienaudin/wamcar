<?php

namespace AppBundle\Command\EntityBuilder;


use AppBundle\Doctrine\Entity\ProVehiclePicture;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Wamcar\Garage\Garage;
use Wamcar\Vehicle\ProVehicle;

abstract class ProVehicleBuilder
{

    /** @var LoggerInterface */
    private $logger;

    /**
     * ProVehicleBuilder constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array|\SimpleXMLElement $vehicleDTORowData Vehicle data from the row or XML
     * @param Garage $garage The garage of the vehicle
     * @param null|ProVehicle $existingProVehicle The vehicle to update or null
     * @return ProVehicle
     */
    public abstract function generateVehicleFromRowData($vehicleDTORowData, Garage $garage, ?ProVehicle $existingProVehicle = null): ProVehicle;

    /**
     * Add a picture to the ProVehicle from the given URL
     * @param ProVehicle $proVehicle
     * @param string $url
     * @param int $position
     * @return true if the picture is accessible and successfully added
     */
    protected function addProVehiclePictureFormUrl(ProVehicle $proVehicle, string $url, int $position)
    {
        $originalFileName = basename($url);
        $tempLocation = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('vehiclePicture') . '_' . $originalFileName;
        try {
            $urlFile = fopen($url, "r");
            if ($urlFile !== FALSE) {
                if (file_put_contents($tempLocation, $urlFile) !== false) {
                    $uploadedFile = new UploadedFile($tempLocation, $originalFileName, mime_content_type($tempLocation), filesize($tempLocation), null, true);
                    $vehiclePicture = new ProVehiclePicture(null, $proVehicle, $uploadedFile, null, $position);
                    $proVehicle->addPicture($vehiclePicture);
                    return true;
                } else {
                    $this->logger->warning('file_put_contents(' . $tempLocation . ') returns FALSE');
                }
                fclose($urlFile);
            } else {
                $this->logger->warning('fopen <' . $url . '> returns an FALSE');
            }
        } catch (FileNotFoundException $fileNotFoundException) {
            $this->logger->warning($fileNotFoundException->getMessage());
        }catch(\Exception $e){
            $this->logger->warning($e->getMessage());
        }
        return false;
    }

    /**
     * Add a picture to the ProVehicle from a local file
     * @param ProVehicle $proVehicle
     * @param string $pictureDirectory
     * @param string $pictureFilename
     * @param int $position
     */
    protected static function addPictureToProVehicle(ProVehicle $proVehicle, string $pictureDirectory, string $pictureFilename, int $position)
    {
        if (!empty($pictureFilename)) {
            $picturePathname = $pictureDirectory . $pictureFilename;
            if (file_exists($picturePathname)) {
                $uploadedFile = new UploadedFile($picturePathname, $pictureFilename, mime_content_type($picturePathname), filesize($picturePathname), null, true);
                $vehiclePicture = new ProVehiclePicture(null, $proVehicle, $uploadedFile, null, $position);
                $proVehicle->addPicture($vehiclePicture);
            }
        }
    }
}