<?php


namespace AppBundle\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Wamcar\VideoCoaching\ScriptSection;
use Wamcar\VideoCoaching\ScriptSequence;
use Wamcar\VideoCoaching\ScriptVersion;

class VideoCoachingExtension extends AbstractExtension
{
    /**
     * @return array|\Twig\TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('dialogueDuration', [$this, 'estimateDialogueDuration']),
            new TwigFilter('scriptDuration', [$this, 'estimateScriptVersionDuration'])
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

    /**
     * Estime la durée de diction de l'ensemble du script
     * @param ScriptVersion $scriptVersion
     * @return float
     */
    public function estimateScriptVersionDuration(ScriptVersion $scriptVersion)
    {
        $duration = 0;
        /** @var ScriptSection $scriptSection */
        foreach ($scriptVersion->getScriptSections() as $scriptSection) {
            /** @var ScriptSequence $scriptSequence */
            foreach ($scriptSection->getScriptSequences() as $scriptSequence) {
                if (!empty($scriptSequence->getDialogue())) {
                    $duration += round(str_word_count($scriptSequence->getDialogue()) / 2);
                }
            }
        }
        return $duration;
    }
}
