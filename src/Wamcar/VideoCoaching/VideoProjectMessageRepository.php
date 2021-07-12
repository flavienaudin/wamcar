<?php


namespace Wamcar\VideoCoaching;


interface VideoProjectMessageRepository
{
    /** {@inheritdoc} */
    public function add(VideoProjectMessage $videoProjectMessage): void;

    /** {@inheritdoc} */
    public function update(VideoProjectMessage $videoProjectMessage): void;

    /** {@inheritdoc} */
    public function remove(VideoProjectMessage $videoProjectMessage): void;
}