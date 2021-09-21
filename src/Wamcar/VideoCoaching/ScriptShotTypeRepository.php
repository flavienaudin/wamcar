<?php


namespace Wamcar\VideoCoaching;


interface ScriptShotTypeRepository
{
    /** {@inheritdoc} */
    public function add(ScriptShotType $scriptShotType): void;

    /**
     * {@inheritdoc}
     */
    public function update(ScriptShotType $scriptShotType): void;

    /**
     * {@inheritdoc}
     */
    public function remove(ScriptShotType $scriptShotType): void;
}