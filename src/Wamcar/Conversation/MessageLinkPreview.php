<?php


namespace Wamcar\Conversation;


class MessageLinkPreview extends BaseLinkPreview
{
    /** @var Message */
    private $message;

    /** {@inheritdoc} */
    public function setOwner(ContentWithLinkPreview $contentWithLinkPreview){
        if($contentWithLinkPreview instanceof Message) {
            $this->setMessage($contentWithLinkPreview);
        }
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * @param Message $message
     */
    public function setMessage(Message $message): void
    {
        $this->message = $message;
    }
}