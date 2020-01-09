<?php


namespace AppBundle\Form\DTO;


use Wamcar\User\ProUser;

class ProPresentationVideoDTO
{
    /** @var string */
    public $youtubeVideoUrl;
    /** @var string */
    public $videoTitle;
    /** @var string */
    public $videoText;

    /**
     * ProPresentationVideoDTO constructor.
     * @param ProUser $proUSer
     */
    public function __construct(ProUser $proUSer)
    {
        $this->youtubeVideoUrl = $proUSer->getYoutubeVideoUrl();
        $this->videoTitle = $proUSer->getVideoTitle();
        $this->videoText = $proUSer->getVideoText();
    }
}