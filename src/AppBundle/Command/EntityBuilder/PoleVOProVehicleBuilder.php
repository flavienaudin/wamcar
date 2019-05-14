<?php


namespace AppBundle\Command\EntityBuilder;


use AppBundle\Doctrine\Entity\ProVehiclePicture;
use AppBundle\Exception\Vehicle\VehicleImportRGFailedException;
use Wamcar\Garage\Garage;
use Wamcar\Vehicle\Engine;
use Wamcar\Vehicle\Enum\Transmission;
use Wamcar\Vehicle\Fuel;
use Wamcar\Vehicle\Make;
use Wamcar\Vehicle\Model;
use Wamcar\Vehicle\ModelVersion;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\Registration;

class PoleVOProVehicleBuilder extends ProVehicleBuilder
{
    const REFERENCE_PREFIX = 'wamcar_polevo_';

    const CHILDNAME_VEHICLE = "annonce";
    const CHILDNAME_ID = "car_id";
    const CHILDNAME_MODEL_NAME = "model";
    const CHILDNAME_MAKE_NAME = "make";
    const CHILDNAME_FULL_MODEL_VERSION = "name";
    const CHILDNAME_ENERGY = "fuel";
    const CHILDNAME_TRANSMISSION = "gear_box";
    const AUTO_TRANSMISSION_VAUE = "auto_gb";
    const MANUAL_TRANSMISSION_VAUE = "manual_gb";
    const CHILDNAME_PRICE = "price";
    const CHILDNAME_CATALOGUE_PRICE = "dealer_price";
    const CHILDNAME_REGISTRATION_DATE = "release";
    const CHILDNAME_MILEAGE = "kilometer";
    const CHILDNAME_DESCRIPTION = "details";
    const CHILDNAME_OPTIONS = "options";
    const CHILDNAME_PICTURES = "photos";
    const CHILDNAME_PICTURE = "photo";
    const CHILDNAME_CREATED_AT = "created_at";
    const CHILDNAME_UPDATED_AT = "updated_at";

    /**
     * @@inheritDoc
     */
    public function generateVehicleFromRowData($vehicleDTORowData, Garage $garage, ?ProVehicle $existingProVehicle = null): ProVehicle
    {
        if (empty($vehicleDTORowData->{self::CHILDNAME_MODEL_NAME}) || empty($vehicleDTORowData->{self::CHILDNAME_MAKE_NAME}) ||
            empty($vehicleDTORowData->{self::CHILDNAME_FULL_MODEL_VERSION}) || empty($vehicleDTORowData->{self::CHILDNAME_ENERGY})) {
            // RG-TRI-Oblig-Modele
            throw new VehicleImportRGFailedException('RG-TRI-Oblig-Modele', sprintf("Modele : %s ; Make : %s ; Full Model Version : %s ; Fuel : %s",
                $vehicleDTORowData->{self::CHILDNAME_MODEL_NAME}, $vehicleDTORowData->{self::CHILDNAME_MAKE_NAME}, $vehicleDTORowData->{self::CHILDNAME_FULL_MODEL_VERSION}, $vehicleDTORowData->{self::CHILDNAME_ENERGY}
            ));
        } else {
            $engineName = trim(str_replace(
                    [$vehicleDTORowData->{self::CHILDNAME_MAKE_NAME}, $vehicleDTORowData->{self::CHILDNAME_MODEL_NAME}],
                    ['', ''],
                    $vehicleDTORowData->{self::CHILDNAME_FULL_MODEL_VERSION})
            );
            $modelVersion = new ModelVersion($vehicleDTORowData->{self::CHILDNAME_FULL_MODEL_VERSION},
                new Model($vehicleDTORowData->{self::CHILDNAME_MODEL_NAME}, new Make($vehicleDTORowData->{self::CHILDNAME_MAKE_NAME})),
                new Engine($engineName, new Fuel(ucfirst($vehicleDTORowData->{self::CHILDNAME_ENERGY})))
            );
        }

        if (empty($vehicleDTORowData->{self::CHILDNAME_PRICE})) {
            // RG-TRI-Oblig-Prix
            throw new VehicleImportRGFailedException('RG-TRI-Oblig-Prix');
        } else {
            $price = floatval($vehicleDTORowData->{self::CHILDNAME_PRICE});
        }

        if (empty($vehicleDTORowData->{self::CHILDNAME_MILEAGE})) {
            // RG-TRI-Oblig-Km
            throw new VehicleImportRGFailedException('RG-TRI-Oblig-Km');
        } else {
            $mileage = intval($vehicleDTORowData->{self::CHILDNAME_MILEAGE}->__toString());
        }

        if (strval($vehicleDTORowData->{self::CHILDNAME_TRANSMISSION}) === self::AUTO_TRANSMISSION_VAUE) {
            $transmission = Transmission::TRANSMISSION_AUTOMATIC();
        } else {
            $transmission = Transmission::TRANSMISSION_MANUAL();
        }

        $registration = new Registration(null, null, null);
        $registrationDate = new \DateTime($vehicleDTORowData->{self::CHILDNAME_REGISTRATION_DATE}->__toString());

        $additionalInformation = !empty($vehicleDTORowData->{self::CHILDNAME_DESCRIPTION}) ?
            str_replace(' -', PHP_EOL, $vehicleDTORowData->{self::CHILDNAME_DESCRIPTION})
            . PHP_EOL . PHP_EOL
            : '';
        $options = join(PHP_EOL, array_map(function ($option) {
                return ucfirst(strtolower($option));
            }, explode('|', $vehicleDTORowData->{self::CHILDNAME_OPTIONS})));
        if(!empty($options)){
            $additionalInformation .= "Options : " . PHP_EOL . $options . PHP_EOL . PHP_EOL;
        }
        $additionalInformation .= 'Référence : ' . self::REFERENCE_PREFIX . $vehicleDTORowData->{self::CHILDNAME_ID};

        if ($existingProVehicle != null) {
            $proVehicle = $existingProVehicle;
            $proVehicle->setModelVersion($modelVersion);
            $proVehicle->setTransmission($transmission);
            $proVehicle->setRegistration($registration);
            $proVehicle->setRegistrationDate($registrationDate);
            $proVehicle->setIsUsed(true);
            $proVehicle->setMileage($mileage);
            $proVehicle->setAdditionalInformation($additionalInformation);
            $proVehicle->setPrice($price);
            $proVehicle->setCreatedAt(new \DateTime($vehicleDTORowData->{self::CHILDNAME_CREATED_AT}));
            $proVehicle->setUpdatedAt(new \DateTime($vehicleDTORowData->{self::CHILDNAME_UPDATED_AT}));

            $photos = [];
            $updateVehiclePictures = false;
            $position = 0;
            /** @var ProVehiclePicture[] $proVehiclePictures */
            $proVehiclePictures = $proVehicle->getPictures();
            if (count($vehicleDTORowData->{self::CHILDNAME_PICTURES}) > 0 &&
                count($vehicleDTORowData->{self::CHILDNAME_PICTURES}[0]->{self::CHILDNAME_PICTURE}) > 0) {
                foreach ($vehicleDTORowData->{self::CHILDNAME_PICTURES}[0]->{self::CHILDNAME_PICTURE} as $picture) {
                    $photoUrl = trim(strval($picture));
                    $photoUrl = explode('?', $photoUrl)[0];
                    $photos[$position] = $photoUrl;
                    $updateVehiclePictures = $updateVehiclePictures || !isset($proVehiclePictures[$position]) || $proVehiclePictures[$position]->getFileOriginalName() != basename($photoUrl);
                    $position++;
                }
            } else {
                $updateVehiclePictures = true;
            }

            if ($updateVehiclePictures) {
                $proVehicle->clearPictures();
                $pos = 0;
                foreach ($photos as $photoUrl) {
                    $this->addProVehiclePictureFormUrl($proVehicle, $photoUrl, $pos);
                    $pos++;
                }
            }
        } else {
            $proVehicle = new ProVehicle(
                $modelVersion,
                $transmission,
                $registration,
                $registrationDate,
                true,
                $mileage,
                [],
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $additionalInformation,
                null,
                $price,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                self::REFERENCE_PREFIX . $vehicleDTORowData->{self::CHILDNAME_ID}
            );
            $proVehicle->setCreatedAt(new \DateTime($vehicleDTORowData->{self::CHILDNAME_CREATED_AT}));
            $proVehicle->setUpdatedAt(new \DateTime($vehicleDTORowData->{self::CHILDNAME_UPDATED_AT}));

            $position = 0;
            if (count($vehicleDTORowData->{self::CHILDNAME_PICTURES}) > 0 &&
                count($vehicleDTORowData->{self::CHILDNAME_PICTURES}[0]->{self::CHILDNAME_PICTURE}) > 0) {
                foreach ($vehicleDTORowData->{self::CHILDNAME_PICTURES}[0]->{self::CHILDNAME_PICTURE} as $picture) {
                    $photoUrl = trim(strval($picture));
                    $photoUrl = explode('?', $photoUrl)[0];
                    $this->addProVehiclePictureFormUrl($proVehicle, $photoUrl, $position);
                    $position++;
                }
            }

            $proVehicle->setGarage($garage);
            // TODO Tirage aléatoire en attendant implémentation des règles
            $members = $garage->getAvailableSellers()->toArray();
            $proVehicle->setSeller($members[array_rand($members)]->getProUser());

        }
        return $proVehicle;
    }
}