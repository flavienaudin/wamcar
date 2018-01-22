<?php


namespace AppBundle\Twig;

use AppBundle\Utils\StarsChoice;
use Twig\Extension\AbstractExtension;

class StarsExtension extends AbstractExtension
{

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('starValueLabel', array($this, 'starValueLabelFilter'))
        );
    }

    public function starValueLabelFilter(string $value)
    {
        $starValueLabels = StarsChoice::getStarsArray(true, true);

        return $starValueLabels[$value];
    }
}
