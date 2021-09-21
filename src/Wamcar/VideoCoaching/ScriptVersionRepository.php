<?php


namespace Wamcar\VideoCoaching;


interface ScriptVersionRepository
{
    /** {@inheritdoc} */
    public function add(ScriptVersion $scriptVersion): void;

    /** {@inheritdoc} */
    public function update(ScriptVersion $scriptVersion): void;

    /** {@inheritdoc} */
    public function remove(ScriptVersion $scriptVersion): void;
}