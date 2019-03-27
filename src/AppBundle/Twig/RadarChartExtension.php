<?php

namespace AppBundle\Twig;


use AppBundle\Doctrine\Entity\AffinityDegree;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RadarChartExtension extends AbstractExtension
{

    public function getFunctions()
    {
        return [
            new TwigFunction('emptyRadarChartData', [$this, 'getEmptyRadarChartData']),
        ];
    }

    public function getEmptyRadarChartData(): array
    {
        return AffinityDegree::getEmptyRadarChartData();
    }

}