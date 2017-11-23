<?php

namespace AppBundle\Api\DTO;
use AppBundle\Services\Vehicle\CanBeProVehicle;

/**
 * @SWG\Definition(
 *   definition="Vehicule",
 *   type="object"
 * )
 */
final class VehicleDTO implements CanBeProVehicle
{
    /** @SWG\Property(type="integer", format="int64") */
    public $IdentifiantVehicule;
    /** @SWG\Property(type="string", format="date") */
    public $Date1Mec;
    /** @SWG\Property(type="string") */
    public $Marque;
    /** @SWG\Property(type="string", enum={"citadine","berline","break","monospace","suv","cabriolet","utilitaire","sans_permis"}) */
    public $Type;
    /** @SWG\Property(type="string") */
    public $Motorisation;
    /** @SWG\Property(type="string") */
    public $Modele;
    /** @SWG\Property(type="string") */
    public $Version;
    /** @SWG\Property(type="string", enum={"Essence","Diesel","Hybride","Electrique","GPL"}) */
    public $Energie;
    /** @SWG\Property(type="integer", format="int32") */
    public $Kilometrage;
    /** @SWG\Property(type="integer", format="int32") */
    public $PrixVenteTTC;
    /** @SWG\Property(type="boolean") */
    public $Neuf;
    /** @SWG\Property(type="string") */
    public $Description;
    /** @SWG\Property(type="string") */
    public $URLVehicule;
    /** @SWG\Property(type="string", format="date-fullyear") */
    public $Annee;
    /** @SWG\Property(type="string") */
    public $Famille;
    /** @SWG\Property(type="integer", format="int32") */
    public $NbPlaces;
    /** @SWG\Property(type="integer", format="int32") */
    public $NbPortes;
    /** @SWG\Property(type="string") */
    public $Couleur;
    /** @SWG\Property(type="string", enum={"BVA","BVAS","BVM","BVMS","BVR","BVRD","CVT","E","I","N/D"}) */
    public $BoiteLibelle;
    /** @SWG\Property(type="string") */
    public $GarantieLibelle;
    /** @SWG\Property(type="string") */
    public $EquipementsSerieEtOption;

    /**
     * @param string $jsonData
     * @return VehicleDTO
     */
    public static function createFromJson(string $jsonData): self
    {
        $vehicleDto = new self();
        $data = json_decode($jsonData, true);

        $vehicleDto->IdentifiantVehicule = $data['IdentifiantVehicule'];
        $vehicleDto->Date1Mec = $data['Date1Mec'];
        $vehicleDto->Marque = $data['Marque'];
        $vehicleDto->Type = $data['Type'];
        $vehicleDto->Motorisation = $data['Motorisation'];
        $vehicleDto->Modele = $data['Modele'];
        $vehicleDto->Version = $data['Version'];
        $vehicleDto->Energie = $data['Energie'];
        $vehicleDto->Kilometrage = $data['Kilometrage'];
        $vehicleDto->PrixVenteTTC = $data['PrixVenteTTC'];
        $vehicleDto->Neuf = $data['Neuf'] ?? null;
        $vehicleDto->Description = $data['Description'];
        $vehicleDto->URLVehicule = $data['URLVehicule'] ?? null;
        $vehicleDto->Annee = $data['Annee'] ?? null;
        $vehicleDto->Famille = $data['Famille'] ?? null;
        $vehicleDto->NbPlaces = $data['NbPlaces'] ?? null;
        $vehicleDto->NbPortes = $data['NbPortes'] ?? null;
        $vehicleDto->Couleur = $data['Couleur'] ?? null;
        $vehicleDto->BoiteLibelle = $data['BoiteLibelle'] ?? null;
        $vehicleDto->GarantieLibelle = $data['GarantieLibelle'] ?? null;
        $vehicleDto->EquipementsSerieEtOption = $data['EquipementsSerieEtOption'] ?? null;

        return $vehicleDto;
    }
}
