<?php

namespace AppBundle\Api\DTO;

/**
 * @SWG\Definition(
 *   definition="Vehicule",
 *   type="object"
 * )
 */
class VehicleDTO
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
}
