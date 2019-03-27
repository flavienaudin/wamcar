<?php


namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Wamcar\Vehicle\Enum\SaleStatus;

class SaleStatusExtension extends AbstractExtension
{

    public function getFunctions()
    {
        return [
            new TwigFunction('saleStatus', [$this, 'saleStatusToArray'])
        ];
    }

    public function saleStatusToArray()
    {
        return SaleStatus::toArray();
    }
}
