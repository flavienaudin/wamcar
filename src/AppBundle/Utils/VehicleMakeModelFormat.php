<?php


namespace AppBundle\Utils;


class VehicleMakeModelFormat
{
    /*
     * Les mots de 2 lettres et moins, sont en majuscules
     */
    const MAKE_BLACK_LIST = [];
    const MODEL_BLACK_LIST = [
        // Roman number
        'i','ii','iii','iv','v','vi','vii','viii','ix','x','xi','xii','xiii','xiv','xv','xvi','xvii','xviii','xix','xx',
        // Generic,
        'suv',
        // Acura
        'zdx'
    ];

}