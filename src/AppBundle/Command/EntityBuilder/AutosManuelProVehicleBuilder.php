<?php

namespace AppBundle\Command\EntityBuilder;


use AppBundle\Doctrine\Entity\ProVehiclePicture;
use AppBundle\Exception\Vehicle\VehicleImportRGFailedException;
use Wamcar\Garage\Garage;
use Wamcar\Vehicle\Engine;
use Wamcar\Vehicle\Enum\Guarantee;
use Wamcar\Vehicle\Enum\Transmission;
use Wamcar\Vehicle\Fuel;
use Wamcar\Vehicle\Make;
use Wamcar\Vehicle\Model;
use Wamcar\Vehicle\ModelVersion;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\Registration;

/**
 * ProVehicle builder from AutosManuel data
 * Class AutosManuelProVehicleBuilder
 * @package AppBundle\Command\EntityBuilder
 */
class AutosManuelProVehicleBuilder extends ProVehicleBuilder
{
    const REFERENCE_PREFIX = 'wamcar_autosmanuel_';

    const FIELDNAME_REFERENCE = 'num_police';
    const FIELDNAME_RECIPIENT = 'code_destinataire';
    const FIELDNAME_ENABLED = 'actif';
    const RECIPIENT_ACCEPTED_VALUE = 'P';
    const FIELDNAME_GARAGE_CODE = 'code_etablissement';

    const FIELDNAME_INTERNET_PRICE = 'prix_internet';
    const FIELDNAME_MAIN_PRICE = 'prix_vente';
    const FIELDNAME_GUARANTEE = 'garantie_duree';
    const FIELDNAME_OTHER_GUARANTEE = 'garantie_nom';

    const FIELDNAME_MODELVERSION_MODEL_MAKE_NAME = 'nom_marque';
    const FIELDNAME_MODELVERSION_MODEL_NAME = 'nom_modele';
    const FIELDNAME_MODELVERSION_ENGINE_NAME = 'nom_version';
    const FIELDNAME_MODELVERSION_ENGINE_FUEL_CODE = 'code_energie';
    const FIELDNAME_MODELVERSION_ENGINE_FUEL_NAME = 'nom_energie';
    const FIELDNAME_TRANSMISSION = 'boite_automatique';

    const FIELDNAME_MILEAGE = 'kilometrage';

    const FIELDNAME_IMMATRICULATION = 'immatriculation';
    const FIELDNAME_VIN = 'numero_chassis';
    const FIELDNAME_REGISTRATION_DATE = 'date_mec';

    // La date correspond à la date de mise à jour chez Koredge = date d'exécution du script
    //const FIELDNAME_UPDATED_AT = 'date_update';

    // Description
    const FIELDNAME_GENRE = "code_genre_vehicule";
    const FIELDNAME_CAR_BODY = "nom_carrosserie";
    const FIELDNAME_FISCAL_POWER = "puissance_fiscale";
    const FIELDNAME_SEATS_NUMBER = "nombre_places";
    const FIELDNAME_DOORS_NUMBER = "nombre_portes";
    const FIELDNAME_GEARS_NUMBER = "nombre_rapports";
    const FIELDNAME_COLOR = "code_couleur";
    const FIELDNAME_CO2_RATE = "taux_co2";
    const FIELDNAME_DIN_POWER = "puissance_din";
    const FIELDNAME_AVERAGE_CONSUMPTION = "consommation_moyenne";
    const FIELDNAME_SITE = "code_site";

    const FIELDNAME_AVAILABILITY_DATE = "date_disponibilite";
    const FIELDNAME_SELLER_CONTACT = "description_vendeur";

    const FIELDNAME_OPTIONS = "options";
    const FIELDNAME_PHOTOS = "photos";

    /**
     * @@inheritDoc
     */
    public function generateVehicleFromRowData($vehicleDTORowData, Garage $garage, ?ProVehicle $existingProVehicle = null): ProVehicle
    {
        // RG-TRAIT-AM-Energie
        $fuelName = self::getFuelName($vehicleDTORowData[self::FIELDNAME_MODELVERSION_ENGINE_FUEL_NAME], $vehicleDTORowData[self::FIELDNAME_MODELVERSION_ENGINE_FUEL_CODE]);
        $modelVersion = new ModelVersion(null,
            new Model ($vehicleDTORowData[self::FIELDNAME_MODELVERSION_MODEL_NAME], new Make($vehicleDTORowData[self::FIELDNAME_MODELVERSION_MODEL_MAKE_NAME])),
            new Engine($vehicleDTORowData[self::FIELDNAME_MODELVERSION_ENGINE_NAME], new Fuel($fuelName)));

        if (intval($vehicleDTORowData[self::FIELDNAME_TRANSMISSION]) === 1) {
            $transmission = Transmission::TRANSMISSION_AUTOMATIC();
        } else {
            $transmission = Transmission::TRANSMISSION_MANUAL();
        }

        $registration = new Registration(null, $vehicleDTORowData[self::FIELDNAME_IMMATRICULATION] ?? null, $vehicleDTORowData[self::FIELDNAME_VIN ?? null]);
        $registrationDate = new \DateTime($vehicleDTORowData[self::FIELDNAME_REGISTRATION_DATE]);

        $additionalInformation = '';
        if (!empty($vehicleDTORowData[self::FIELDNAME_GENRE])) {
            // Genre (VU ⇒ Véhicule utilitaire / VP ⇒ Véhicule particulier)
            $additionalInformation .= 'Véhicule ' . (strtolower($vehicleDTORowData[self::FIELDNAME_GENRE]) == "vu" ? "utilitaire" : "particulier") . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::FIELDNAME_MODELVERSION_ENGINE_NAME])) {
            // Modèle et version
            $additionalInformation .= 'Version : ' . $vehicleDTORowData[self::FIELDNAME_MODELVERSION_ENGINE_NAME] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::FIELDNAME_CAR_BODY])) {
            // Carrosserie (Berline/Break/…)
            $additionalInformation .= 'Carrosserie : ' . ucfirst($vehicleDTORowData[self::FIELDNAME_CAR_BODY]) . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::FIELDNAME_FISCAL_POWER])) {
            // Puissance Fiscale
            $additionalInformation .= 'Puissance Fiscale : ' . $vehicleDTORowData[self::FIELDNAME_FISCAL_POWER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::FIELDNAME_SEATS_NUMBER])) {
            // Nombre de places
            $additionalInformation .= 'Nombre de places : ' . $vehicleDTORowData[self::FIELDNAME_SEATS_NUMBER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::FIELDNAME_DOORS_NUMBER])) {
            // Nombre de portes
            $additionalInformation .= 'Nombre de portes : ' . $vehicleDTORowData[self::FIELDNAME_DOORS_NUMBER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::FIELDNAME_COLOR])) {
            // Couleur extérieure
            $additionalInformation .= 'Couleur extérieure : ' . $vehicleDTORowData[self::FIELDNAME_COLOR] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::FIELDNAME_CO2_RATE]) && $vehicleDTORowData[self::FIELDNAME_CO2_RATE] > 0) {
            // Taux CO2
            $additionalInformation .= 'Taux CO2 : ' . $vehicleDTORowData[self::FIELDNAME_CO2_RATE] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::FIELDNAME_DIN_POWER]) && $vehicleDTORowData[self::FIELDNAME_DIN_POWER] > 0) {
            // Puissance DIN
            $additionalInformation .= 'Puissance (DIN) : ' . $vehicleDTORowData[self::FIELDNAME_DIN_POWER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::FIELDNAME_GEARS_NUMBER])) {
            // Nb rapport de boîte
            $additionalInformation .= 'Nombre de rapports de boîte : ' . $vehicleDTORowData[self::FIELDNAME_GEARS_NUMBER] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::FIELDNAME_AVERAGE_CONSUMPTION])) {
            // Consommation moyenne
            $additionalInformation .= 'Consommation moyenne : ' . $vehicleDTORowData[self::FIELDNAME_AVERAGE_CONSUMPTION] . PHP_EOL;
        }

        // Options : si option n°1 ne contient pas “ATTENTION”
        // Options : liste des options renseignées (non vides), séparées par une ‘ | ’
        if (!empty($vehicleDTORowData[self::FIELDNAME_OPTIONS])
            && is_array($vehicleDTORowData[self::FIELDNAME_OPTIONS])
            && count($vehicleDTORowData[self::FIELDNAME_OPTIONS]) > 0) {
            $options = join(PHP_EOL, $vehicleDTORowData[self::FIELDNAME_OPTIONS]);
            if (!empty($options)) {
                $additionalInformation .= "Options :" . PHP_EOL . $options . PHP_EOL . PHP_EOL;
            }
        }

        if (!empty($vehicleDTORowData[self::FIELDNAME_SITE])) {
            // Ce véhicule est visible sur le site “site”
            $additionalInformation .= 'Ce véhicule est visible sur le site de ' . $vehicleDTORowData[self::FIELDNAME_SITE] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::FIELDNAME_SELLER_CONTACT])) {
            // Contact vendeur
            $additionalInformation .= 'Contact vendeur : ' . $vehicleDTORowData[self::FIELDNAME_SELLER_CONTACT] . PHP_EOL;
        }
        if (!empty($vehicleDTORowData[self::FIELDNAME_REFERENCE])) {
            // Référence
            $additionalInformation .= 'Référence : ' . self::REFERENCE_PREFIX . $vehicleDTORowData[self::FIELDNAME_REFERENCE];
        }
        try {
            // Date non disponible : génération la veille à une heure de bureau aléatoire
            $updateAt = $this->generateYesterdayDateTime();
        }catch (\Exception $e){
            $updateAt = null;
        }

        $price = 0;
        if (!empty($vehicleDTORowData[self::FIELDNAME_INTERNET_PRICE])) {
            $price = $vehicleDTORowData[self::FIELDNAME_INTERNET_PRICE];
        } elseif (!empty($vehicleDTORowData[self::FIELDNAME_MAIN_PRICE])) {
            $price = $vehicleDTORowData[self::FIELDNAME_MAIN_PRICE];
        }

        $guarantee = null;
        if (isset($vehicleDTORowData[self::FIELDNAME_GUARANTEE])) {
            if ($vehicleDTORowData[self::FIELDNAME_GUARANTEE] == 12) {
                $guarantee = Guarantee::GUARANTEE_12_MONTH();
            } elseif ($vehicleDTORowData[self::FIELDNAME_GUARANTEE] == 24) {
                $guarantee = Guarantee::GUARANTEE_24_MONTH();
            } elseif ($vehicleDTORowData[self::FIELDNAME_GUARANTEE] == 36) {
                $guarantee = Guarantee::GUARANTEE_36_MONTH();
            }
        }
        $otherGuarantee = null;
        if (!empty($vehicleDTORowData[self::FIELDNAME_OTHER_GUARANTEE])) {
            $otherGuarantee = $vehicleDTORowData[self::FIELDNAME_OTHER_GUARANTEE];
        }

        if ($existingProVehicle != null) {
            $proVehicle = $existingProVehicle;
            $proVehicle->setModelVersion($modelVersion);
            $proVehicle->setTransmission($transmission);
            $proVehicle->setRegistration($registration);
            $proVehicle->setRegistrationDate($registrationDate);
            $proVehicle->setIsUsed(true);
            $proVehicle->setMileage($vehicleDTORowData[self::FIELDNAME_MILEAGE]);
            $proVehicle->setAdditionalInformation($additionalInformation);
            $proVehicle->setPrice($price);
            $proVehicle->setGuarantee($guarantee);
            $proVehicle->setOtherGuarantee($otherGuarantee);
            $proVehicle->setReference(self::REFERENCE_PREFIX . $vehicleDTORowData[self::FIELDNAME_REFERENCE]);

            if($updateAt < $proVehicle->getCreatedAt()){
                $proVehicle->setCreatedAt($updateAt);
            }
            $proVehicle->setUpdatedAt($updateAt);


            $photos = [];
            $updateVehiclePictures = false;
            if (isset($vehicleDTORowData[self::FIELDNAME_PHOTOS])) {
                $position = -1;
                /** @var ProVehiclePicture[] $proVehiclePictures */
                $proVehiclePictures = $proVehicle->getPictures();
                foreach ($vehicleDTORowData[self::FIELDNAME_PHOTOS] as $photoUrl) {
                    // On zappe la première photo car contient le numéro de téléphone
                    if($position >= 0){
                        $photos[$position] = $photoUrl;
                        $updateVehiclePictures = $updateVehiclePictures || !isset($proVehiclePictures[$position]) || $proVehiclePictures[$position]->getFileOriginalName() != basename($photoUrl);
                    }
                    $position++;
                }
            } else {
                $updateVehiclePictures = true;
            }
            if ($updateVehiclePictures) {
                $proVehicle->clearPictures();
                $pos = 0;
                foreach ($photos as $photoUrl) {
                    if($this->addProVehiclePictureFormUrl($proVehicle, $photoUrl, $pos)) {
                        $pos++;
                    }
                }
            }
        } else {
            $proVehicle = new ProVehicle(
                $modelVersion,
                $transmission,
                $registration,
                $registrationDate,
                true,
                $vehicleDTORowData[self::FIELDNAME_MILEAGE],
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
                self::REFERENCE_PREFIX . $vehicleDTORowData[self::FIELDNAME_REFERENCE]
            );

            $proVehicle->setCreatedAt($updateAt);
            $proVehicle->setUpdatedAt($updateAt);

            if (isset($vehicleDTORowData[self::FIELDNAME_PHOTOS])) {
                $position = -1;
                foreach ($vehicleDTORowData[self::FIELDNAME_PHOTOS] as $photoUrl) {
                    // On zappe la première photo car contient le numéro de téléphone
                    if($position >= 0) {
                        if($this->addProVehiclePictureFormUrl($proVehicle, $photoUrl, $position)){
                            $position++;
                        }
                    }else {
                        $position++;
                    }
                }
            }

            $proVehicle->setGarage($garage);
        }

        return $proVehicle;
    }

    private static function getFuelName(string $dataFuelName, string $dataFuelCode): string
    {
        if ($dataFuelName === 'Hybride Diesel Electrique') {
            return 'Hybride';
        }
        return $dataFuelName ?? self::translateEnergy($dataFuelCode);
    }

    private static function translateEnergy(string $input, string $default = null, bool $toAbbreviation = false): string
    {
        $fuels = [
            "ES" => "Essence",
            "GO" => "Diesel",
            "GP" => "Gpl",
            "EE" => "Electrique",
            "HDE" => "Hybride",
            "ELECT" => "Electrique",
            "HEE" => "Hybride",
            "HGE" => "Hybride",
            "HHY" => "Hybride"
        ];
        if ($toAbbreviation) {
            $fuels = array_flip($fuels);
        }
        return $fuels[$input] ?? $default ?? $input;
    }
}