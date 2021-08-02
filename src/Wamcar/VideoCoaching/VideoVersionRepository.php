<?php


namespace Wamcar\VideoCoaching;


interface VideoVersionRepository
{
    /** {@inheritdoc} */
    public function add(VideoVersion $videoVersion): void;

    /** {@inheritdoc} */
    public function update(VideoVersion $videoVersion): void;

    /** {@inheritdoc} */
    public function remove(VideoVersion $videoVersion): void;
}