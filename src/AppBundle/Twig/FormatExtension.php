<?php


namespace AppBundle\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FormatExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('phoneFormat', function (string $value) {
                return join('-', str_split($value, 2));
            })
        ];
    }
}