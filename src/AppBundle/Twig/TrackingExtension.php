<?php


namespace AppBundle\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Wamcar\User\BaseUser;

class TrackingExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('wtUserDataAttrValue', array($this, 'getWtUserDataAttrValue'))
        ];
    }

    /**
     * Get the value of attribute data-from//data-to for GA tracking
     * @param BaseUser|null $user
     * @return string
     */
    public function getWtUserDataAttrValue(?BaseUser $user): string
    {
        if ($user == null) {
            return "0";
        }
        if ($user->isPro()) {
            return "Advisor" . $user->getId();
        } elseif ($user->isPersonal()) {
            return "Customer" . $user->getId();
        }
    }
}