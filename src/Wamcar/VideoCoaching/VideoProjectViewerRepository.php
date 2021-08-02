<?php


namespace Wamcar\VideoCoaching;


interface VideoProjectViewerRepository
{
    /** {@inheritdoc} */
    public function add(VideoProjectViewer $videoProjectViewer);

    /** {@inheritdoc} */
    public function update(VideoProjectViewer $videoProjectViewer);

    /** {@inheritdoc} */
    public function remove(VideoProjectViewer $videoProjectViewer);
}