<?php


namespace AppBundle\Utils;


class StarsChoice
{
    const LABEL_STARS = [
        'Très mauvais' => '5',
        'Mauvais' => '4',
        'Moyen' => '3',
        'Bon' => '2',
        'Très bon' => '1'
    ];
    const LABEL_STARS_VIEW = [
        'Très mauvais' => '1',
        'Mauvais' => '2',
        'Moyen' => '3',
        'Bon' => '4',
        'Très bon' => '5'
    ];

    /**
     * @param bool $flipKeyValue
     * @param bool $view
     * @return array
     */
    public static function getStarsArray($flipKeyValue = false, $view = false): array
    {
        $stars = $view ? self::LABEL_STARS_VIEW : self::LABEL_STARS;

        if ($flipKeyValue) {
            $stars = array_flip($stars);
        }

        return $stars;
    }
}
