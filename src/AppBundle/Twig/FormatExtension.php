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
            new TwigFilter('phoneFormat', function (string $value) {
                return join('-', str_split($value, 2));
            }),
            new TwigFilter('vehicleMakeFormat', [$this, 'vehicleMakeModelFormat']),
            new TwigFilter('vehicleModelFormat', [$this, 'vehicleMakeModelFormat']),
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
                $result .= '-';
            }
            if (in_array(strtolower($w), $blacklist) || strlen($w) <= 2) {
                $result .= strtoupper($w);
            } else {
                $result .= ucfirst(strtolower($w));
            }
        }
        return $result;
    }
}