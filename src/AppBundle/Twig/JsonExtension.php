<?php


namespace AppBundle\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class JsonExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('json_decode', function ($str) {
                return json_decode($str);
            }),
        ];
    }
}