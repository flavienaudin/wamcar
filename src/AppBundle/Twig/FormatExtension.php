<?php


namespace AppBundle\Twig;


use AppBundle\Utils\VehicleMakeModelFormat;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FormatExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('phoneFormat', [$this, 'phoneFormat']),
            new TwigFilter('vehicleMakeFormat', [$this, 'vehicleMakeModelFormat']),
            new TwigFilter('vehicleModelFormat', [$this, 'vehicleMakeModelFormat']),
            new TwigFilter('durationFormat', [$this, 'formatDuration']),
        ];
    }


    /**
     * Format the make or model value : first letter uppecase, rest lower case, except for blacklist keywords
     * @param string $value
     * @param string $type
     * @return string
     */
    public function vehicleMakeModelFormat(string $value, string $type)
    {
        $words = explode(' ', $value);
        $result = '';
        if ($type === 'make') {
            $blacklist = VehicleMakeModelFormat::MAKE_BLACK_LIST;
        } else {
            $blacklist = VehicleMakeModelFormat::MODEL_BLACK_LIST;
        }
        foreach ($words as $w) {
            if (!empty($result)) {
                $result .= ' ';
            }
            if (in_array(strtolower($w), $blacklist) || strlen($w) <= 2) {
                $result .= strtoupper($w);
            } else {
                $result .= ucfirst(strtolower($w));
            }
        }
        return $result;
    }

    /**
     * Format a phone number xx-xx-xx-xx-xx
     * @param string $value
     * @return string
     */
    public static function phoneFormat(string $value)
    {
        return join('-', str_split($value, 2));
    }

    /**
     * Format a duration in seconds into [$Hours h][$min min] $sec s
     * @param $duration
     * @return string
     */
    public function formatDuration($duration)
    {
        $pieces = [];
        $hours = intdiv($duration, 3600);
        $duration = $duration % 3600;
        $minutes = intdiv($duration, 60);
        $duration = $duration % 60;
        $seconds = $duration;

        if (!empty($hours)) {
            $pieces[] = $hours;
            $pieces[] = 'h';
        }
        if (!empty($minutes) || !empty($pieces)) {
            $pieces[] = $minutes;
            $pieces[] = ' min';
        }
        $pieces[] = $seconds;
        $pieces[] = 's';

        return join(' ', $pieces);
    }
}