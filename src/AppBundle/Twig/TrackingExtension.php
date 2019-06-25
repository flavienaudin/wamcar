<?php


namespace AppBundle\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Wamcar\Garage\Garage;
use Wamcar\User\BaseUser;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;

class TrackingExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('wtFromDataAttrValue', array($this, 'getWtFromDataAttrValue')),
            new TwigFilter('wtToDataAttrValue', array($this, 'getWtToDataAttrValue'))
        ];
    }

    /**
     * Get the value of attribute data-from to for [GA] tracking
     * @param BaseUser|null $from
     * @return string
     */
    public function getWtFromDataAttrValue(?BaseUser $from): string
    {
        if ($from == null) {
            return "0";
        }
        if ($from->isPro()) {
            return "Advisor" . $from->getId();
        } elseif ($from->isPersonal()) {
            return "Customer" . $from->getId();
        }
        return "0";
    }

    /**
     * Get the value of attribute data-from to for [GA] tracking
     * @param null|mixed $to
     * @return string
     */
    public function getWtToDataAttrValue($to): string
    {
        if ($to == null) {
            return "0";
        }
        if ($to instanceof ProUser) {
            return "Advisor" . $to->getId();
        } elseif ($to instanceof PersonalUser) {
            return "Customer" . $to->getId();
        } elseif ($to instanceof Garage) {
            return "Garage" . $to->getId();
        }
    }
}