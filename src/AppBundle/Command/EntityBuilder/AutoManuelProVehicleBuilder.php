<?php

namespace AppBundle\Command\EntityBuilder;


use AppBundle\Doctrine\Entity\ProVehiclePicture;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Wamcar\Garage\Garage;
use Wamcar\Vehicle\Engine;
use Wamcar\Vehicle\Enum\Transmission;
use Wamcar\Vehicle\Fuel;
use Wamcar\Vehicle\Make;
use Wamcar\Vehicle\Model;
use Wamcar\Vehicle\ModelVersion;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\Registration;

class AutoManuelProVehicleBuilder
{

    // VO Indexes
    const IDX_VO_REFERENCE = 1;
    const IDX_VO_REGISTRATION_DATE = 4;
    const IDX_VO_GENRE = 5;
    const IDX_VO_MAKE = 6;
    const IDX_VO_CAR_BODY = 8;
    const IDX_VO_ENERGIE = 9;
    const IDX_VO_FISCAL_POWER = 10;
    const IDX_VO_SEATS_NUMBER = 11;
    const IDX_VO_MODEL_VERSION = 12;
    const IDX_VO_MILEAGE = 14;
    const IDX_VO_EXTERIOR_COLOR = 17;
    const IDX_VO_MAIN_PRICE = 19;
    const IDX_VO_MONTH_GUARANTEE = 21;
    const IDX_VO_LABEL_GUARANTEE = 22;
    const IDX_VO_INTERNET_PRICE = 23;
    const IDX_VO_FREE_COMMENT = 24;
    const IDX_VO_MIN_PRICE = 25;
    const IDX_VO_SELLER_PRICE = 26;
    const IDX_VO_GARAGE_CODE = 27;
    const IDX_VO_MODEL = 47;
    const IDX_VO_VERSION = 48;
    const IDX_VO_DOORS_NUMBER = 53;
    const IDX_VO_TRANSMISSION = 54;
    const IDX_VO_CO2_RATE = 55;
    const IDX_VO_DIN_POWER = 61;
    const IDX_VO_GEARS_NUMBER = 62;
    const IDX_VO_AVERAGE_CONSUMPTION = 63;
    const IDX_VO_IMMATRICULATION = 69;
    const IDX_VO_SELLER_CONTACT = 73;
    const IDX_VO_START_1_OPTION = 82;
    const IDX_VO_END_1_OPTION = 111;
    const IDX_VO_RECIPIENT = 114;
    const IDX_VO_SITE = 115;
    const VO_ACCEPTED_RECIPIENT_PARTICULIER = 'P';
    const IDX_VO_START_1_PICTURE = 117;
    const IDX_VO_END_1_PICTURE = 124;
    const IDX_VO_START_2_PICTURE = 127;
    const IDX_VO_END_2_PICTURE = 133;
    const IDX_VO_VIN = 135;
    const IDX_VO_START_2_OPTION = 136;
    const IDX_VO_END_2_OPTION = 155;

    // VN indexes
    const IDX_VN_NUM_POLICE = 1;
    const IDX_VN_DEMONSTRATION_DATE = 4;
    const IDX_VN_GENRE = 5;
    const IDX_VN_MAKE = 6;
    const IDX_VN_CAR_BODY = 8;
    const IDX_VN_FISCAL_POWER = 10;
    const IDX_VN_MODEL_VERSION = 12;
    const IDX_VN_MILEAGE = 14;
    const IDX_VN_EXTERIOR_COLOR = 17;
    const IDX_VN_INTERIOR_COLOR = 18;
    const IDX_VN_MAIN_PRICE = 21;
    const IDX_VN_GARAGE_CODE = 27;
    const IDX_VN_MODEL = 47;
    const IDX_VN_VERSION = 48;
    const IDX_VN_VEHICLE_NATURE = 50;
    const VN_VD = 'VD';
    const IDX_VN_ENERGIE = 51;
    const IDX_VN_CO2_RATE = 52;
    const IDX_VN_INTERNET_PRICE = 53;
    const IDX_VN_DIN_POWER = 54;
    const IDX_VN_TRANSMISSION = 55;
    const VN_TRANSMISSION_AUTOMATIC = 'A';
    const IDX_VN_GEARS_NUMBER = 56;
    const IDX_VN_SEATS_NUMBER = 57;
    const IDX_VN_DOORS_NUMBER = 58;
    const IDX_VN_IMMATRICULATION = 69;
    const IDX_VN_START_1_OPTION = 82;
    const IDX_VN_END_1_OPTION = 111;
    const IDX_VN_SITE = 115;
    const IDX_VN_VIN = 135;
    const IDX_VN_START_2_OPTION = 136;
    const IDX_VN_END_2_OPTION = 155;


    /**
     * @param null|ProVehicle $existingProVehicle The vehicle to update or null
     * @param array $vehicleDTORowData Vehicle data from the row
     * @param Garage $garage The garage of the vehicle
     * @param string $pictureDirectory
     * @return ProVehicle
     */
    public static function generateVehicleFromUsedData(
        ?ProVehicle $existingProVehicle, array $vehicleDTORowData, Garage $garage, string $pictureDirectory
    ): ProVehicle
    {
        $modelVersion = new ModelVersion($vehicleDTORowData[self::IDX_VO_MODEL_VERSION],
            new Model ($vehicleDTORowData[self::IDX_VO_MODEL], new Make($vehicleDTORowData[self::IDX_VO_MAKE])),
            new Engine($vehicleDTORowData[self::IDX_VO_VERSION], new Fuel(self::translateEnergy($vehicleDTORowData[self::IDX_VO_ENERGIE]))));

        if ($vehicleDTORowData[self::IDX_VO_TRANSMISSION] === 0) {
            $transmission = Transmission::MANUAL();
        } else {
            $transmission = Transmission::AUTOMATIC();
        }

        $registration = new Registration(null, $vehicleDTORowData[self::IDX_VO_IMMATRICULATION], $vehicleDTORowData[self::IDX_VO_VIN]);
        $registrationDate = new \DateTime($vehicleDTORowData[self::IDX_VO_REGISTRATION_DATE]);

        $additionalInformation = '';
        if (!empty($vehicleDTORowData[self::IDX_VO_GENRE])) {
            // Genre (5 VU ⇒ Véhicule utilitaire / VP ⇒ Véhicule particulier)
            $additionalInformation .= 'Véhicule ' . (strtolower($vehicleDTORowData[self::IDX_VO_GENRE]) == "vu" ? "utilitaire" : "particulier") . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VO_MODEL_VERSION])) {
            // Modèle et version (12)
            $additionalInformation .= 'Version : ' . $vehicleDTORowData[self::IDX_VO_MODEL_VERSION] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VO_CAR_BODY])) {
            // Carrosserie (8 Berline/Break/…)
            $additionalInformation .= 'Carrosserie : ' . $vehicleDTORowData[self::IDX_VO_CAR_BODY] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VO_FISCAL_POWER])) {
            // Puissance Fiscale (10)
            $additionalInformation .= 'Puissance Fiscale : ' . $vehicleDTORowData[self::IDX_VO_FISCAL_POWER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VO_SEATS_NUMBER])) {
            // Nombre de places (11)
            $additionalInformation .= 'Nombre de places : ' . $vehicleDTORowData[self::IDX_VO_SEATS_NUMBER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VO_DOORS_NUMBER])) {
            // Nombre de portes (53)
            $additionalInformation .= 'Nombre de portes : ' . $vehicleDTORowData[self::IDX_VO_DOORS_NUMBER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VO_EXTERIOR_COLOR])) {
            // Couleur extérieure (17)
            $additionalInformation .= 'Couleur extérieure : ' . $vehicleDTORowData[self::IDX_VO_EXTERIOR_COLOR] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VO_CO2_RATE])) {
            // Taux CO2 (55)
            $additionalInformation .= 'Taux CO2 : ' . $vehicleDTORowData[self::IDX_VO_CO2_RATE] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VO_DIN_POWER])) {
            // Puissance DIN (61)
            $additionalInformation .= 'Puissance (DIN) : ' . $vehicleDTORowData[self::IDX_VO_DIN_POWER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VO_GEARS_NUMBER])) {
            // Nb rapport de boîte (62)
            $additionalInformation .= 'Nombre de rapports de boîte : ' . $vehicleDTORowData[self::IDX_VO_GEARS_NUMBER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VO_AVERAGE_CONSUMPTION])) {
            // Consommation moyenne (63)
            $additionalInformation .= 'Consommation moyenne : ' . $vehicleDTORowData[self::IDX_VO_AVERAGE_CONSUMPTION] . PHP_EOL;
        }

        // Options n°1 (82) → n°30 (111) : si option n°1 ne contient pas “ATTENTION”
        // Options n°31 (136) → n°50 (155)
        // Options : liste des options renseignées (non vides), séparées par une ‘,’
        if (strpos($vehicleDTORowData[self::IDX_VO_START_1_OPTION], 'ATTENTION') === FALSE) {
            $options = '';
            for ($i = self::IDX_VO_START_1_OPTION; $i <= self::IDX_VO_END_1_OPTION; $i++) {
                if (!empty($vehicleDTORowData[$i])) {
                    $options .= (strlen($options) > 0 ? ' | ' : '') . $vehicleDTORowData[$i];
                }
            }
            for ($i = self::IDX_VO_START_2_OPTION; $i <= self::IDX_VO_END_2_OPTION; $i++) {
                if (!empty($vehicleDTORowData[$i])) {
                    $options .= (strlen($options) > 0 ? ' | ' : '') . $vehicleDTORowData[$i];
                }
            }
            if (!empty($options)) {
                $additionalInformation .= "Options : " . $options . PHP_EOL;
            }
        }

        if (!empty($vehicleDTORowData[self::IDX_VO_SITE])) {
            // Ce véhicule est visible sur le site “site (115)”
            $additionalInformation .= 'Ce véhicule est visible sur le site de ' . $vehicleDTORowData[self::IDX_VO_SITE] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VO_FREE_COMMENT])) {
            // Zone commentaire libre internet (24)
            $additionalInformation .= $vehicleDTORowData[self::IDX_VO_FREE_COMMENT] . PHP_EOL;
        }

        if (!empty($vehicleDTORowData[self::IDX_VO_SELLER_CONTACT])) {
            // Contact vendeur (73)
            $additionalInformation .= 'Contact vendeur : ' . $vehicleDTORowData[self::IDX_VO_SELLER_CONTACT] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VO_REFERENCE])) {
            // Référence (1)
            $additionalInformation .= 'Référence : ' . $vehicleDTORowData[self::IDX_VO_REFERENCE];
        }

        $price = 0;
        if (!empty($vehicleDTORowData[self::IDX_VO_INTERNET_PRICE])) {
            $price = $vehicleDTORowData[self::IDX_VO_INTERNET_PRICE];
        } elseif (!empty($vehicleDTORowData[self::IDX_VO_MAIN_PRICE])) {
            $price = $vehicleDTORowData[self::IDX_VO_MAIN_PRICE];
        }

        // TODO présentation
        $otherGuarantee = (empty($vehicleDTORowData[self::IDX_VO_MONTH_GUARANTEE]) ? '' : $vehicleDTORowData[self::IDX_VO_MONTH_GUARANTEE] . 'mois - ') . $vehicleDTORowData[self::IDX_VO_LABEL_GUARANTEE] . PHP_EOL;

        if ($existingProVehicle != null) {
            $proVehicle = $existingProVehicle;
            $proVehicle->setModelVersion($modelVersion);
            $proVehicle->setTransmission($transmission);
            $proVehicle->setRegistration($registration);
            $proVehicle->setRegistrationDate($registrationDate);
            $proVehicle->setIsUsed(true);
            $proVehicle->setMileage($vehicleDTORowData[self::IDX_VO_MILEAGE]);
            $proVehicle->setAdditionalInformation($additionalInformation);
            $proVehicle->setPrice($price);
            $proVehicle->setOtherGuarantee($otherGuarantee);
            $proVehicle->setReference($vehicleDTORowData[self::IDX_VO_REFERENCE]);

            // Photo n°1 (117) → n°8 (124)
            // Photo n°9 (127) → n°15 (133)
            $position = 0;
            $photos = [];
            /** @var ProVehiclePicture[] $proVehiclePictures */
            $proVehiclePictures = $proVehicle->getPictures();
            $updateVehiclePictures = false;
            for ($p = self::IDX_VO_START_1_PICTURE; $p <= self::IDX_VO_END_1_PICTURE; $p++) {
                $photos[$position] = $vehicleDTORowData[$p];
                $updateVehiclePictures = $updateVehiclePictures || !isset($proVehiclePictures[$position]) || $proVehiclePictures[$position]->getFileOriginalName() != $photos[$position];
                $position++;
            }
            for ($p = self::IDX_VO_START_2_PICTURE; $p <= self::IDX_VO_END_2_PICTURE; $p++) {
                $photos[$position] = $vehicleDTORowData[$p];
                $updateVehiclePictures = $updateVehiclePictures || !isset($proVehiclePictures[$position]) || $proVehiclePictures[$position]->getFileOriginalName() != $photos[$position];
                $position++;
            }
            if ($updateVehiclePictures) {
                $proVehicle->clearPictures();
                $pos = 0;
                foreach ($photos as $photoName) {
                    self::addPictureToProVehicle($proVehicle, $pictureDirectory, $photoName, $pos);
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
                $vehicleDTORowData[self::IDX_VO_MILEAGE],
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
                $otherGuarantee,
                null,
                null,
                null,
                $vehicleDTORowData[self::IDX_VO_REFERENCE]
            );

            // Photo n°1 (117) → n°8 (124)
            // Photo n°9 (127) → n°15 (133)
            $position = 0;
            for ($p = self::IDX_VO_START_1_PICTURE; $p <= self::IDX_VO_END_1_PICTURE; $p++) {
                self::addPictureToProVehicle($proVehicle, $pictureDirectory, $vehicleDTORowData[$p], $position);
                $position++;
            }
            for ($p = self::IDX_VO_START_2_PICTURE; $p <= self::IDX_VO_END_2_PICTURE; $p++) {
                self::addPictureToProVehicle($proVehicle, $pictureDirectory, $vehicleDTORowData[$p], $position);
                $position++;
            }

            $proVehicle->setGarage($garage);
            // TODO Tirage aléatoire en attendant implémentation des règles
            $members = $garage->getEnabledMembers()->toArray();
            $proVehicle->setSeller($members[array_rand($members)]->getProUser());
        }

        return $proVehicle;
    }

    /**
     * @param null|ProVehicle $existingProVehicle The vehicle to update or null
     * @param array $vehicleDTORowData Vehicle data from the row
     * @param Garage $garage The garage of the vehicle
     * @param string $pictureDirectory
     * @return ProVehicle
     */
    public static function generateVehicleFromNewData(
        ?ProVehicle $existingProVehicle, array $vehicleDTORowData, Garage $garage, string $pictureDirectory
    ): ProVehicle
    {
        $modelVersion = new ModelVersion($vehicleDTORowData[self::IDX_VN_MODEL_VERSION],
            new Model ($vehicleDTORowData[self::IDX_VN_MODEL], new Make($vehicleDTORowData[self::IDX_VN_MAKE])),
            new Engine($vehicleDTORowData[self::IDX_VN_VERSION], new Fuel(self::translateEnergy($vehicleDTORowData[self::IDX_VN_ENERGIE]))));

        if ($vehicleDTORowData[self::IDX_VN_TRANSMISSION] === self::VN_TRANSMISSION_AUTOMATIC) {
            $transmission = Transmission::AUTOMATIC();
        } else {
            $transmission = Transmission::MANUAL();
        }

        $registration = new Registration(null, $vehicleDTORowData[self::IDX_VN_IMMATRICULATION], $vehicleDTORowData[self::IDX_VN_VIN]);
        // TODO : Check for VN if it is the commercialisation date ?
        $registrationDate = new \DateTime($vehicleDTORowData[self::IDX_VN_DEMONSTRATION_DATE]);

        $additionalInformation = '';
        if ($vehicleDTORowData[self::IDX_VN_VEHICLE_NATURE] == self::VN_VD) {
            $additionalInformation .= 'Véhicle de démonstration';
            if ($vehicleDTORowData[self::IDX_VN_DEMONSTRATION_DATE]) {
                $additionalInformation .= ' depuis le ' . $vehicleDTORowData[self::IDX_VN_DEMONSTRATION_DATE];
            }
            $additionalInformation .= PHP_EOL;

            $mileage = $vehicleDTORowData[self::IDX_VN_MILEAGE];
        } else {
            $mileage = 10;
        }
        if (!empty($vehicleDTORowData[self::IDX_VN_GENRE])) {
            // Genre (5 VU ⇒ Véhicule utilitaire / VP ⇒ Véhicule particulier)
            $additionalInformation .= 'Véhicule ' . (strtolower($vehicleDTORowData[self::IDX_VN_GENRE]) == "vu" ? "utilitaire" : "particulier") . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VN_MODEL_VERSION])) {
            // Modèle et version (12)
            $additionalInformation .= 'Version : ' . $vehicleDTORowData[self::IDX_VN_MODEL_VERSION] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VN_CAR_BODY])) {
            // Carrosserie (8 Berline/Break/…)
            $additionalInformation .= 'Carrosserie : ' . $vehicleDTORowData[self::IDX_VN_CAR_BODY] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VN_FISCAL_POWER])) {
            // Puissance Fiscale (10)
            $additionalInformation .= 'Puissance Fiscale : ' . $vehicleDTORowData[self::IDX_VN_FISCAL_POWER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VN_SEATS_NUMBER])) {
            // Nombre de places (57)
            $additionalInformation .= 'Nombre de places : ' . $vehicleDTORowData[self::IDX_VN_SEATS_NUMBER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VN_DOORS_NUMBER])) {
            // Nombre de portes (58)
            $additionalInformation .= 'Nombre de portes : ' . $vehicleDTORowData[self::IDX_VN_DOORS_NUMBER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VN_EXTERIOR_COLOR])) {
            // Couleur extérieure (17)
            $additionalInformation .= 'Couleur extérieure : ' . $vehicleDTORowData[self::IDX_VN_EXTERIOR_COLOR] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VN_INTERIOR_COLOR])) {
            // Couleur extérieure (18)
            $additionalInformation .= 'Couleur intérieure : ' . $vehicleDTORowData[self::IDX_VN_INTERIOR_COLOR] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VN_CO2_RATE])) {
            // Taux CO2 (52)
            $additionalInformation .= 'Taux CO2 : ' . $vehicleDTORowData[self::IDX_VN_CO2_RATE] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VN_DIN_POWER])) {
            // Puissance DIN (54)
            $additionalInformation .= 'Puissance (DIN) : ' . $vehicleDTORowData[self::IDX_VN_DIN_POWER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::IDX_VN_GEARS_NUMBER])) {
            // Nb rapport de boîte (56)
            $additionalInformation .= 'Nombre de rapports de boîte : ' . $vehicleDTORowData[self::IDX_VN_GEARS_NUMBER] . PHP_EOL;
        }
        // Options n°1 (82) → n°30 (111) : si option n°1 ne contient pas “ATTENTION”
        // Options n°31 (136) → n°50 (155)
        // Options : liste des options renseignées (non vides), séparées par une ‘,’
        if (strpos($vehicleDTORowData[self::IDX_VN_START_1_OPTION], 'ATTENTION') === FALSE) {
            $options = '';
            for ($i = self::IDX_VN_START_1_OPTION; $i <= self::IDX_VN_END_1_OPTION; $i++) {
                if (!empty($vehicleDTORowData[$i])) {
                    $options .= (strlen($options) > 0 ? ' | ' : '') . $vehicleDTORowData[$i];
                }
            }
            for ($i = self::IDX_VN_START_2_OPTION; $i <= self::IDX_VN_END_2_OPTION; $i++) {
                if (!empty($vehicleDTORowData[$i])) {
                    $options .= (strlen($options) > 0 ? ' | ' : '') . $vehicleDTORowData[$i];
                }
            }
            if (!empty($options)) {
                $additionalInformation .= "Options : " . $options . PHP_EOL;
            }
        }
        if (!empty($vehicleDTORowData[self::IDX_VN_SITE])) {
            // Ce véhicule est visible sur le site “site (115)”
            $additionalInformation .= 'Ce véhicule est visible sur le site de ' . $vehicleDTORowData[self::IDX_VN_SITE] . PHP_EOL;
        }

        if (!empty($vehicleDTORowData[self::IDX_VN_NUM_POLICE])) {
            // Référence (1)
            $additionalInformation .= 'Référence : ' . $vehicleDTORowData[self::IDX_VN_NUM_POLICE];
        }

        $price = 0;
        if (!empty($vehicleDTORowData[self::IDX_VN_INTERNET_PRICE])) {
            $price = $vehicleDTORowData[self::IDX_VN_INTERNET_PRICE];
        } elseif (!empty($vehicleDTORowData[self::IDX_VN_MAIN_PRICE])) {
            $price = $vehicleDTORowData[self::IDX_VN_MAIN_PRICE];
        }

        if ($existingProVehicle != null) {
            $proVehicle = $existingProVehicle;
            $proVehicle->setModelVersion($modelVersion);
            $proVehicle->setTransmission($transmission);
            $proVehicle->setRegistration($registration);
            $proVehicle->setRegistrationDate($registrationDate);
            $proVehicle->setIsUsed(false);
            $proVehicle->setMileage($mileage);
            $proVehicle->setAdditionalInformation($additionalInformation);
            $proVehicle->setPrice($price);
            $proVehicle->setReference($vehicleDTORowData[self::IDX_VN_NUM_POLICE]);

            // ? Photo n°1 (117) → n°8 (124)
            // ? Photo n°9 (127) → n°15 (133)
            /*$position = 0;
            $photos = [];
            /** @var ProVehiclePicture[] $proVehiclePictures *
            $proVehiclePictures = $proVehicle->getPictures();
            $updateVehiclePictures = false;
            for ($p = self::IDX_VN_START_1_PICTURE; $p <= self::IDX_VN_END_1_PICTURE; $p++) {
                $photos[$position] = $vehicleDTORowData[$p];
                $updateVehiclePictures = $updateVehiclePictures || !isset($proVehiclePictures[$position]) || $proVehiclePictures[$position]->getFileOriginalName() != $photos[$position];
                $position++;
            }
            for ($p = self::IDX_VN_START_2_PICTURE; $p <= self::IDX_VN_END_2_PICTURE; $p++) {
                $photos[$position] = $vehicleDTORowData[$p];
                $updateVehiclePictures = $updateVehiclePictures || !isset($proVehiclePictures[$position]) || $proVehiclePictures[$position]->getFileOriginalName() != $photos[$position];
                $position++;
            }
            if ($updateVehiclePictures) {
                $proVehicle->clearPictures();
                $pos = 0;
                foreach ($photos as $photoName) {
                    self::addPictureToProVehicle($proVehicle, $pictureDirectory, $photoName, $pos);
                    $pos++;
                }
            }*/
        } else {
            $proVehicle = new ProVehicle(
                $modelVersion,
                $transmission,
                $registration,
                $registrationDate,
                false,
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
                $vehicleDTORowData[self::IDX_VN_NUM_POLICE]
            );

            // ? Photo n°1 (117) → n°8 (124)
            // ? Photo n°9 (127) → n°15 (133)
            /*$position = 0;
            for ($p = self::IDX_VN_START_1_PICTURE; $p <= self::IDX_VN_END_1_PICTURE; $p++) {
                self::addPictureToProVehicle($proVehicle, $pictureDirectory, $vehicleDTORowData[$p], $position);
                $position++;
            }
            for ($p = self::IDX_VN_START_2_PICTURE; $p <= self::IDX_VN_END_2_PICTURE; $p++) {
                self::addPictureToProVehicle($proVehicle, $pictureDirectory, $vehicleDTORowData[$p], $position);
                $position++;
            }*/

            $proVehicle->setGarage($garage);
            // TODO Tirage aléatoire en attendant implémentation des règles
            $members = $garage->getEnabledMembers()->toArray();
            $proVehicle->setSeller($members[array_rand($members)]->getProUser());
        }

        return $proVehicle;
    }

    private static function addPictureToProVehicle(ProVehicle $proVehicle, string $pictureDirectory, string $pictureFilename, int $position)
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

    private static function translateEnergy(string $input, bool $toAbbreviation = false): string
    {
        $fuels = [
            "ES" => "Energie Essence",
            "GO" => "Gasoil",
            "GP" => "Gpl",
            "EE" => "Courant électrique",
            "HEE" => "Hybride Essence / Courant Electrique",
            "HDE" => "Hybride Diesel / Courant Electrique",
            "HGE" => "Hybride Gaz / Courant Electrique",
            "HHY" => "Hydrogène"
        ];
        if ($toAbbreviation) {
            $fuels = array_flip($fuels);
        }
        return $fuels[$input];
    }
}