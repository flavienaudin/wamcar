<?php


namespace Wamcar\VideoCoaching;


interface VideoProjectRepository
{
    /** {@inheritdoc} */
    public function add(VideoProject $videoProject): void;

    /** {@inheritdoc} */
    public function update(VideoProject $videoProject): void;

    /** {@inheritdoc} */
    public function remove(VideoProject $videoProject): void;
}