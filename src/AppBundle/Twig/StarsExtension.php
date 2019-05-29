<?php


namespace AppBundle\Twig;

use AppBundle\Utils\StarsChoice;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StarsExtension extends AbstractExtension
{

    public function getFilters()
    {
        return array(
            new TwigFilter('starValueLabel', array($this, 'starValueLabelFilter'))
        );
    }

    public function starValueLabelFilter(string $value)
    {
        $starValueLabels = StarsChoice::getStarsArray(true, true);

        return $starValueLabels[$value];
    }
}
