<?php


namespace AppBundle\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class VideoCoachingExtension extends AbstractExtension
{
    /**
     * @return array|\Twig\TwigFunction[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('dialogueDuration', [$this, 'estimateDialogueDuration'])
        ];
    }

    /**
     * Estime la durée de diction du texte
     * @param string|null $value
     * @return float
     */
    public function estimateDialogueDuration(?string $value)
    {
        if (empty($value)) {
            return 0;
        } else {
            return round(str_word_count($value) / 2);
        }
    }
}
