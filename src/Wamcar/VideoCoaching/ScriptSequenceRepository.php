<?php


namespace Wamcar\VideoCoaching;


interface ScriptSequenceRepository
{
    /** {@inheritdoc} */
    public function add(ScriptSequence $scriptSequence): void;

    /** {@inheritdoc} */
    public function update(ScriptSequence $scriptSequence): void;

    /** {@inheritdoc} */
    public function remove(ScriptSequence $scriptSequence): void;
}