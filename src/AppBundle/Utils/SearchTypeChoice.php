<?php

namespace AppBundle\Utils;


class SearchTypeChoice
{
    const SEARCH_PRO_VEHICLE = 'PRO_VEHICLE';
    const SEARCH_PERSONAL_VEHICLE = 'PERSONAL_VEHICLE';
    const SEARCH_PERSONAL_PROJECT = 'PERSONAL_PROJECT';

    /**
     * Return a list of mileage
     *
     * @return array
     */
    public static function getTypeChoice(): array
    {
        return [
            self::SEARCH_PRO_VEHICLE => self::SEARCH_PRO_VEHICLE,
            self::SEARCH_PERSONAL_VEHICLE => self::SEARCH_PERSONAL_VEHICLE,
            self::SEARCH_PERSONAL_PROJECT => self::SEARCH_PERSONAL_PROJECT
        ];
    }
}