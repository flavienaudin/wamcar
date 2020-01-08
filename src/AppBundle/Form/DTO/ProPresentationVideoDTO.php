<?php


namespace AppBundle\Form\DTO;


use Wamcar\User\ProUser;

class ProPresentationVideoDTO
{
    /** @var string */
    public $youtubeVideoUrl;
    public $shortText;
    public $longText;

    /**
     * ProPresentationVideoDTO constructor.
     * @param ProUser $proUSer
     */
    public function __construct(ProUser $proUSer)
    {
        $this->youtubeVideoUrl = $proUSer->getYoutubeVideoUrl();
        $this->shortText = $proUSer->getVideoShortText();
        $this->longText = $proUSer->getVideoText();
    }


}