<?php


namespace AppBundle\Command\EntityBuilder;


use AppBundle\Doctrine\Entity\ProVehiclePicture;
use AppBundle\Exception\Vehicle\VehicleImportInvalidDataException;
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

class AutoBonPlanProVehicleBuilder extends ProVehicleBuilder
{
    const REFERENCE_PREFIX = 'wamcar_autobonplan_';

    const CHILDNAME_VEHICLE = "Vehicule";
    const CHILDNAME_REFERENCE = "ReferenceVehicule";
    const CHILDNAME_REGISTRATION_DATE = "Date1Mec";
    const CHILDNAME_REGISTRATION_VIN = "NumeroSerie";
    const CHILDNAME_REGISTRATION_PLATENUMBER = "Immatriculation";
    const CHILDNAME_CREATED_AT = "DateCreat";


    const CHILDNAME_MAKE_NAME = "Marque";
    // = Famille + Version
    const CHILDNAME_MODEL_NAME = "Modele";
    // ModelVersion.Model.name
    const CHILDNAME_MODEL_VERSION_NAME = "Famille";
    // ModelVersion.Engine.name
    const CHILDNAME_MODEL_ENGINE = "Version";
    const CHILDNAME_ENERGY = "EnergieLibelle";
    const CHILDNAME_MILEAGE = "Kilometrage";
    const USED_VEHICLE_MIN_MILEAGE = 50;
    const CHILDNAME_TRANSMISSION = "BoiteLibelle";
    const AUTO_TRANSMISSION_VAUE = "Automatique";
    const MANUAL_TRANSMISSION_VAUE = "Manuelle";

    const CHILDNAME_PRICE = "PrixVenteTTC";
    const CHILDNAME_DISCOUNT = "Remise";
    const CHILDNAME_GUARANTEE = "GarantieConstructeur";
    const CHILDNAME_OTHER_GUARANTEE = "GarantieLibelle";
    const CHILDNAME_OTHER_GUARANTEE_2 = "Garantie";
    const CHILDNAME_PICTURES = "UrlPhotoAutobonplan";

    // description
    const CHILDNAME_FISCAL_POWER = "PuissanceFiscale";
    const CHILDNAME_HORSE_POWER = "PuissanceReelle";
    const CHILDNAME_SEATS_NUMBER = "NbPlaces";
    const CHILDNAME_DOORS_NUMBER = "NbPortes";
    const CHILDNAME_COLOR = "Couleur";
    const CHILDNAME_GEARS_NUMBER = "NbRapports";
    const CHILDNAME_CAR_BODY = "CategorieLibelle";

    const CHILDNAME_STANDARD_EQUIPMENTS_AND_OPTIONS = "EquipementsSerieEtOption";
    const CHILDNAME_STANDARD_EQUIPMENTS = "EquipementsSerie";
    const CHILDNAME_OPTIONAL_EQUIPMENTS = "EquipementsOption";
    const CHILDNAME_PRICE_WITH_OPTIONS = "MontantOptionsIncluses";
    const CHILDNAME_MISSING_EQUIPMENTS = "EquipementsManquants";
    const CHILDNAME_MISSING_EQUIPMENTS_PRICE = "MontantEquipementsManquants";
    const CHILDNAME_ALL_DIMENSIONS = "PoidsEtDimensions";
    const CHILDNAME_DIMENSION_VOLUME = "Volume";
    const CHILDNAME_DIMENSION_FUELTANK_CAPACITY = "CapaciteReservoir";
    const CHILDNAME_DIMENSION_LENGTH = "Longueur";
    const CHILDNAME_DIMENSION_WIDTH = "Largeur";
    const CHILDNAME_DIMENSION_HEIGHT = "Hauteur";
    const CHILDNAME_DIMENSION_WHEELBASE = "Empattement";
    const CHILDNAME_DIMENSION_WEIGHT = "Poids";
    const CHILDNAME_DIMENSION_CYLINDRE = "Cylindre";

    const CHILDNAME_CO2_RATE = "Co2";
    const CHILDNAME_MIXED_CONSUMPTION = "ConsommationMixte";
    const CHILDNAME_URBAN_CONSUMPTION = "ConsommationUrbaine";
    const CHILDNAME_HIGHWAY_CONSUMPTION = "ConsommationExtraUrbaine";
    const CHILDNAME_KM_GUARANTEE = "KmGaranti";
    const CHILDNAME_SITE_LOCALISATION = "Site";

    /**
     * @@inheritDoc
     */
    public function generateVehicleFromRowData($vehicleDTORowData, Garage $garage, ?ProVehicle $existingProVehicle = null): ProVehicle
    {
        if (empty($vehicleDTORowData->{self::CHILDNAME_MAKE_NAME}) ||
            empty($vehicleDTORowData->{self::CHILDNAME_MODEL_VERSION_NAME}) ||
            empty($vehicleDTORowData->{self::CHILDNAME_MODEL_ENGINE}) ||
            empty($vehicleDTORowData->{self::CHILDNAME_ENERGY})) {
            // RG-TRI-Oblig-Modele
            throw new VehicleImportRGFailedException('RG-TRI-Oblig-Modele',
                sprintf("Make : %s; Modele : %s; Engine : %s; Fuel : %s",
                    $vehicleDTORowData->{self::CHILDNAME_MAKE_NAME},
                    $vehicleDTORowData->{self::CHILDNAME_MODEL_VERSION_NAME},
                    $vehicleDTORowData->{self::CHILDNAME_MODEL_ENGINE},
                    $vehicleDTORowData->{self::CHILDNAME_ENERGY}
                ));
        } else {
            $modelVersion = new ModelVersion(
                $vehicleDTORowData->{self::CHILDNAME_MODEL_NAME} . ' ' . $vehicleDTORowData->{self::CHILDNAME_MODEL_NAME},
                new Model($vehicleDTORowData->{self::CHILDNAME_MODEL_VERSION_NAME}, new Make($vehicleDTORowData->{self::CHILDNAME_MAKE_NAME})),
                new Engine($vehicleDTORowData->{self::CHILDNAME_MODEL_ENGINE}, new Fuel(ucfirst($vehicleDTORowData->{self::CHILDNAME_ENERGY})))
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
            $mileage = intval($vehicleDTORowData->{self::CHILDNAME_MILEAGE});
            // RG-TRAIT-ABP-Statut
            $isUsed = $mileage > self::USED_VEHICLE_MIN_MILEAGE;
        }

        if (strval($vehicleDTORowData->{self::CHILDNAME_TRANSMISSION}) === self::AUTO_TRANSMISSION_VAUE) {
            $transmission = Transmission::TRANSMISSION_AUTOMATIC();
        } else {
            $transmission = Transmission::TRANSMISSION_MANUAL();
        }

        // Dates de création/mise à jour
        $createdAt = null;
        $updatedAt = null;
        if ($existingProVehicle != null) {
            $createdAt = $existingProVehicle->getCreatedAt();
            try {
                $updatedAt = $this->generateYesterdayDateTime();
                if ($updatedAt < $createdAt) {
                    $createdAt = $updatedAt;
                }
            } catch (\Exception $e) {
                $updatedAt = null;
            }
        } else {
            if (!empty($vehicleDTORowData->{self::CHILDNAME_CREATED_AT})) {
                $createdAt = date_create_from_format('Y-m-d', $vehicleDTORowData->{self::CHILDNAME_CREATED_AT});
                $hour = rand(8, 20);
                $minute = rand(0, 59);
                $createdAt->setTime($hour, $minute);
            } else {
                try {
                    $createdAt = $this->generateYesterdayDateTime();
                } catch (\Exception $e) {
                    $createdAt = null;
                }
            }
            $updatedAt = $createdAt;
        }

        // Discount
        $discount = null;
        if (!empty($vehicleDTORowData->{self::CHILDNAME_DISCOUNT}) && floatval($vehicleDTORowData->{self::CHILDNAME_DISCOUNT}) > 0) {
            $discount = floatval($vehicleDTORowData->{self::CHILDNAME_DISCOUNT});
        }
        // Garuantees
        $guarantee = null;
        if (!empty($vehicleDTORowData->{self::CHILDNAME_GUARANTEE})) {
            if ($vehicleDTORowData[self::CHILDNAME_GUARANTEE] == 12) {
                $guarantee = Guarantee::GUARANTEE_12_MONTH();
            } elseif ($vehicleDTORowData[self::CHILDNAME_GUARANTEE] == 24) {
                $guarantee = Guarantee::GUARANTEE_24_MONTH();
            } elseif ($vehicleDTORowData[self::CHILDNAME_GUARANTEE] == 36) {
                $guarantee = Guarantee::GUARANTEE_36_MONTH();
            }
        }
        $otherGuarantee = null;
        if (!empty($vehicleDTORowData->{self::CHILDNAME_OTHER_GUARANTEE})) {
            $otherGuarantee = $vehicleDTORowData->{self::CHILDNAME_OTHER_GUARANTEE} . PHP_EOL;
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_OTHER_GUARANTEE_2})) {
            $otherGuarantee .= $vehicleDTORowData->{self::CHILDNAME_OTHER_GUARANTEE_2};
        }

        $registration = new Registration(null, null, null);
        // VIN
        if (!empty($vehicleDTORowData->{self::CHILDNAME_REGISTRATION_VIN}) &&
            preg_match('/^[\_A-HJ-NPR-Z\d]{17}$/i', $vehicleDTORowData->{self::CHILDNAME_REGISTRATION_VIN}) === 1) {
            $registration->setVin($vehicleDTORowData->{self::CHILDNAME_REGISTRATION_VIN});
        }
        // PlateNumber
        if (!empty($vehicleDTORowData->{self::CHILDNAME_REGISTRATION_PLATENUMBER})) {
            $registration->setPlateNumber($vehicleDTORowData->{self::CHILDNAME_REGISTRATION_PLATENUMBER});
        }

        // Date de 1ere MEC
        $registrationDate = date_create_from_format('d-m-Y', strval($vehicleDTORowData->{self::CHILDNAME_REGISTRATION_DATE}));
        if (!$registrationDate) {
            throw new VehicleImportInvalidDataException("RegistrationDate conversion failed");
        }

        // Marque Modèle et version du véhicule
        $additionalInformation = $vehicleDTORowData->{self::CHILDNAME_MAKE_NAME} . ' ' . $vehicleDTORowData->{self::CHILDNAME_MODEL_NAME} . PHP_EOL;

        if (!empty($vehicleDTORowData->{self::CHILDNAME_SEATS_NUMBER})) {
            // Nombre de places
            $additionalInformation .= 'Nombre de places : ' . $vehicleDTORowData->{self::CHILDNAME_SEATS_NUMBER} . PHP_EOL;
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_DOORS_NUMBER})) {
            // Nombre de portes
            $additionalInformation .= 'Nombre de portes : ' . $vehicleDTORowData->{self::CHILDNAME_DOORS_NUMBER} . PHP_EOL;
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_COLOR})) {
            // Couleur extérieure
            $additionalInformation .= 'Couleur extérieure : ' . $vehicleDTORowData->{self::CHILDNAME_COLOR} . PHP_EOL;
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_CO2_RATE}) && $vehicleDTORowData->{self::CHILDNAME_CO2_RATE} > 0) {
            // Taux CO2
            $additionalInformation .= 'Taux CO2 : ' . $vehicleDTORowData->{self::CHILDNAME_CO2_RATE} . PHP_EOL;
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_HORSE_POWER}) && $vehicleDTORowData->{self::CHILDNAME_HORSE_POWER} > 0) {
            // Puissance réelle
            $additionalInformation .= 'Puissance (CV) : ' . $vehicleDTORowData->{self::CHILDNAME_HORSE_POWER} . PHP_EOL;
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_GEARS_NUMBER})) {
            // Nb rapport de boîte
            $additionalInformation .= 'Nombre de rapports de boîte : ' . $vehicleDTORowData->{self::CHILDNAME_GEARS_NUMBER} . PHP_EOL;
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_MIXED_CONSUMPTION}) && floatval($vehicleDTORowData->{self::CHILDNAME_MIXED_CONSUMPTION}) > 0) {
            // Consommation mixte
            $additionalInformation .= 'Consommation mixte : ' . $vehicleDTORowData->{self::CHILDNAME_MIXED_CONSUMPTION} . ' l/100km' . PHP_EOL;
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_URBAN_CONSUMPTION}) && floatval($vehicleDTORowData->{self::CHILDNAME_URBAN_CONSUMPTION}) > 0) {
            // Consommation urbaine
            $additionalInformation .= 'Consommation urbaine : ' . $vehicleDTORowData->{self::CHILDNAME_URBAN_CONSUMPTION} . ' l/100km' . PHP_EOL;
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_HIGHWAY_CONSUMPTION}) && floatval($vehicleDTORowData->{self::CHILDNAME_HIGHWAY_CONSUMPTION}) > 0) {
            // Consommation extra-urbaine
            $additionalInformation .= 'Consommation extra-urbaine : ' . $vehicleDTORowData->{self::CHILDNAME_HIGHWAY_CONSUMPTION} . ' l/100km' . PHP_EOL;
        }

        if (!empty($vehicleDTORowData->{self::CHILDNAME_STANDARD_EQUIPMENTS_AND_OPTIONS})) {
            // Equipements de série et options
            $additionalInformation .= 'Equipements de série et en option (inclus) : ' .
                str_replace('|', PHP_EOL, $vehicleDTORowData->{self::CHILDNAME_STANDARD_EQUIPMENTS_AND_OPTIONS}) . PHP_EOL;
        } else {
            if (!empty($vehicleDTORowData->{self::CHILDNAME_STANDARD_EQUIPMENTS})) {
                $additionalInformation .= 'Equipements de série : ' .
                    str_replace('|', PHP_EOL, $vehicleDTORowData->{self::CHILDNAME_STANDARD_EQUIPMENTS}) . PHP_EOL;
            }
            if (!empty($vehicleDTORowData->{self::CHILDNAME_OPTIONAL_EQUIPMENTS})) {
                $additionalInformation .= 'Equipements en option (inclus) : ' .
                    str_replace('|', PHP_EOL, $vehicleDTORowData->{self::CHILDNAME_OPTIONAL_EQUIPMENTS}) . PHP_EOL;
            }
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_PRICE_WITH_OPTIONS}) && intval($vehicleDTORowData->{self::CHILDNAME_PRICE_WITH_OPTIONS}) > 0) {
            $additionalInformation .= 'Prix total des équipements en option inclus : ' . intval($vehicleDTORowData->{self::CHILDNAME_PRICE_WITH_OPTIONS}) . '€' . PHP_EOL;
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_MISSING_EQUIPMENTS})) {
            // Equipements de série et options
            $additionalInformation .= 'Equipements en option disponibles sur demande : ' . $vehicleDTORowData->{self::CHILDNAME_MISSING_EQUIPMENTS} . PHP_EOL;
            if (!empty($vehicleDTORowData->{self::CHILDNAME_MISSING_EQUIPMENTS_PRICE}) && intval($vehicleDTORowData->{self::CHILDNAME_MISSING_EQUIPMENTS_PRICE}) > 0) {
                $additionalInformation .= 'Prix total des équipements en option (disponible sur demande): ' . intval($vehicleDTORowData->{self::CHILDNAME_MISSING_EQUIPMENTS_PRICE}) . '€' . PHP_EOL;
            }
        }

        if (!empty($vehicleDTORowData->{self::CHILDNAME_CAR_BODY})) {
            // Puissance Fiscale
            $additionalInformation .= 'Catégorie de véhicule : ' . ucfirst($vehicleDTORowData->{self::CHILDNAME_CAR_BODY}) . PHP_EOL;
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_FISCAL_POWER})) {
            // Puissance Fiscale
            $additionalInformation .= 'Puissance Fiscale : ' . $vehicleDTORowData->{self::CHILDNAME_FISCAL_POWER} . PHP_EOL;
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_ALL_DIMENSIONS})) {
            // Consommation moyenne
            $additionalInformation .= 'Dimensions : ' . PHP_EOL;
            $additionalInformation .= '<ul><li>' . str_replace('|', '</li><li>', $vehicleDTORowData->{self::CHILDNAME_ALL_DIMENSIONS}) . '</li></ul>';
        } else {
            $dimensions = '';
            if (!empty($vehicleDTORowData->{self::CHILDNAME_DIMENSION_VOLUME}) && intval($vehicleDTORowData->{self::CHILDNAME_DIMENSION_VOLUME}) > 0) {
                // Volume du coffre
                $dimensions .= '<li>Capacité du coffre : ' . $vehicleDTORowData->{self::CHILDNAME_DIMENSION_VOLUME} . ' litres</li>';
            }
            if (!empty($vehicleDTORowData->{self::CHILDNAME_DIMENSION_WHEELBASE}) && intval($vehicleDTORowData->{self::CHILDNAME_DIMENSION_WHEELBASE}) > 0) {
                // Empattement
                $dimensions .= '<li>Empattement : ' . $vehicleDTORowData->{self::CHILDNAME_DIMENSION_WHEELBASE} . ' mm</li>';
            }
            if (!empty($vehicleDTORowData->{self::CHILDNAME_DIMENSION_HEIGHT}) && intval($vehicleDTORowData->{self::CHILDNAME_DIMENSION_HEIGHT}) > 0) {
                // Hauteur
                $dimensions .= '<li>Hauteur : ' . $vehicleDTORowData->{self::CHILDNAME_DIMENSION_HEIGHT} . ' mm</li>';
            }
            if (!empty($vehicleDTORowData->{self::CHILDNAME_DIMENSION_WIDTH}) && intval($vehicleDTORowData->{self::CHILDNAME_DIMENSION_WIDTH}) > 0) {
                // Largeur
                $dimensions .= '<li>Largueur : ' . $vehicleDTORowData->{self::CHILDNAME_DIMENSION_WIDTH} . ' mm</li>';
            }
            if (!empty($vehicleDTORowData->{self::CHILDNAME_DIMENSION_LENGTH}) && intval($vehicleDTORowData->{self::CHILDNAME_DIMENSION_LENGTH}) > 0) {
                // Longueur
                $dimensions .= '<li>Longueur : ' . $vehicleDTORowData->{self::CHILDNAME_DIMENSION_LENGTH} . ' mm</li>';
            }
            if (!empty($vehicleDTORowData->{self::CHILDNAME_DIMENSION_WEIGHT}) && intval($vehicleDTORowData->{self::CHILDNAME_DIMENSION_WEIGHT}) > 0) {
                // Poids
                $dimensions .= '<li>Poids : ' . $vehicleDTORowData->{self::CHILDNAME_DIMENSION_WEIGHT} . ' kg</li>';
            }
            if (!empty($vehicleDTORowData->{self::CHILDNAME_DIMENSION_FUELTANK_CAPACITY}) && intval($vehicleDTORowData->{self::CHILDNAME_DIMENSION_FUELTANK_CAPACITY}) > 0) {
                // Réservoir
                $dimensions .= '<li>Réservoir : ' . $vehicleDTORowData->{self::CHILDNAME_DIMENSION_FUELTANK_CAPACITY} . ' litres</li>';
            }
            if (!empty($vehicleDTORowData->{self::CHILDNAME_DIMENSION_CYLINDRE}) && intval($vehicleDTORowData->{self::CHILDNAME_DIMENSION_CYLINDRE}) > 0) {
                // Cylindre
                $dimensions .= '<li>Cylindrée : ' . $vehicleDTORowData->{self::CHILDNAME_DIMENSION_CYLINDRE} . ' cm3</li>';
            }

            if (!empty($dimensions)) {
                $additionalInformation .= 'Dimensions : <ul>' . $dimensions . '</ul>';
            }
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_KM_GUARANTEE})) {
            // Garantie kilomètrage
            $additionalInformation .= 'Garantie kilomètrage : ' . $vehicleDTORowData->{self::CHILDNAME_KM_GUARANTEE} . PHP_EOL;
        }
        if (!empty($vehicleDTORowData->{self::CHILDNAME_SITE_LOCALISATION})) {
            // Garantie kilomètrage
            $additionalInformation .= 'Site : ' . $vehicleDTORowData->{self::CHILDNAME_SITE_LOCALISATION} . PHP_EOL;
        }

        // Référence
        $additionalInformation .= 'Référence : ' . self::REFERENCE_PREFIX . $vehicleDTORowData->{self::CHILDNAME_REFERENCE};

        if ($existingProVehicle != null) {
            $proVehicle = $existingProVehicle;
            $proVehicle->setModelVersion($modelVersion);
            $proVehicle->setTransmission($transmission);
            $proVehicle->setRegistration($registration);
            $proVehicle->setRegistrationDate($registrationDate);
            $proVehicle->setIsUsed($isUsed);
            $proVehicle->setMileage($mileage);
            $proVehicle->setAdditionalInformation($additionalInformation);
            $proVehicle->setPrice($price);
            $proVehicle->setGuarantee($guarantee);
            $proVehicle->setOtherGuarantee($otherGuarantee);
            $proVehicle->setDiscount($discount);
            if ($createdAt != null) {
                $proVehicle->setCreatedAt($createdAt);
            }
            if ($updatedAt != null) {
                $proVehicle->setUpdatedAt($updatedAt);
            }

            $photos = [];
            $updateVehiclePictures = false;
            $position = 0;
            /** @var ProVehiclePicture[] $proVehiclePictures */
            $proVehiclePictures = $proVehicle->getPictures();
            if (!empty(strval($vehicleDTORowData->{self::CHILDNAME_PICTURES}))) {
                $pictures = explode('|', strval($vehicleDTORowData->{self::CHILDNAME_PICTURES}));
                foreach ($pictures as $photoUrl) {
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
                    if ($this->addProVehiclePictureFormUrl($proVehicle, $photoUrl, $pos)) {
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
                $isUsed,
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
                $discount,
                $guarantee,
                $otherGuarantee,
                null,
                null,
                null,
                self::REFERENCE_PREFIX . $vehicleDTORowData->{self::CHILDNAME_REFERENCE}
            );
            if ($createdAt != null) {
                $proVehicle->setCreatedAt($createdAt);
            }
            if ($updatedAt != null) {
                $proVehicle->setUpdatedAt($updatedAt);
            }

            $position = 0;
            if (!empty(strval($vehicleDTORowData->{self::CHILDNAME_PICTURES}))) {
                $pictures = explode('|', strval($vehicleDTORowData->{self::CHILDNAME_PICTURES}));
                foreach ($pictures as $photoUrl) {
                    if ($this->addProVehiclePictureFormUrl($proVehicle, $photoUrl, $position)) {
                        $position++;
                    }
                }
            }
            $proVehicle->setGarage($garage);
            $sellerCandidates = $garage->getBestSellersForVehicle($proVehicle);
            $proVehicle->setSeller($sellerCandidates[array_rand($sellerCandidates)]['seller']);
        }
        return $proVehicle;
    }
}