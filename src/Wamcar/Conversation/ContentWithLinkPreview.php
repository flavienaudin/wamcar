<?php


namespace Wamcar\Conversation;


interface ContentWithLinkPreview
{

    /**
     * Get the content to analyse for link preview
     * @return string
     */
    public function getContent();

    /**
     * @param LinkPreview $linkPreview
     * @return mixed
     */
    public function addLinkPreview(LinkPreview $linkPreview);

    /**
     * @param LinkPreview $linkPreview
     */
    public function removeLinkPreview(LinkPreview $linkPreview): void;
}