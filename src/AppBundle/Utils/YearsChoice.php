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
        $currentYear = date('Y');
        $arrayYear = array_combine(range($currentYear, $currentYear - $nbYears), range($currentYear, $currentYear - $nbYears));

        return $arrayYear;
    }
}
