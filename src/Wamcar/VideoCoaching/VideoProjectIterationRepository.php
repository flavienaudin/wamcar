<?php


namespace Wamcar\VideoCoaching;


interface VideoProjectIterationRepository
{
    /** {@inheritdoc} */
    public function add(VideoProjectIteration $videoProjectIteration): void;

    /** {@inheritdoc} */
    public function update(VideoProjectIteration $videoProjectIteration): void;

    /** {@inheritdoc} */
    public function remove(VideoProjectIteration $videoProjectIteration): void;
}