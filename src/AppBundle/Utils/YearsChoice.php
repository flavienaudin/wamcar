<?php


namespace AppBundle\Utils;


class YearsChoice
{
    /**
     * Return a list of last years
     *
     * @return array
     */
    public static function getLastYears($nbYears = 7)
    {
        $arrayYear = [];
        $currentYear = date('Y');
        for ($i = $nbYears; $i >=0; $i--) {
            $arrayYear[$currentYear - $i] = $currentYear - $i;
        }
        arsort($arrayYear);

        return $arrayYear;
    }
}
