<?php


namespace Wamcar\VideoCoaching;


use Wamcar\Conversation\BaseLinkPreview;
use Wamcar\Conversation\ContentWithLinkPreview;

class VideoProjectMessageLinkPreview extends BaseLinkPreview
{
    /** @var VideoProjectMessage */
    private $message;

    /** {@inheritdoc} */
    public function setOwner(ContentWithLinkPreview $contentWithLinkPreview){
        if($contentWithLinkPreview instanceof VideoProjectMessage) {
            $this->setMessage($contentWithLinkPreview);
        }
    }

    /**
     * @return VideoProjectMessage
     */
    public function getMessage(): VideoProjectMessage
    {
        return $this->message;
    }

    /**
     * @param VideoProjectMessage $videoProjectMessage
     */
    public function setMessage(VideoProjectMessage $videoProjectMessage): void
    {
        $this->message = $videoProjectMessage;
    }

}