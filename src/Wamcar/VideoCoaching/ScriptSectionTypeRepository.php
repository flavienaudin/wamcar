<?php


namespace Wamcar\VideoCoaching;


interface ScriptSectionTypeRepository
{
    /** {@inheritdoc} */
    public function findOneBy(array $criteria, array $orderBy = null);

    /** {@inheritdoc} */
    public function add(ScriptSectionType $scriptSectionType): void;

    /** {@inheritdoc} */
    public function update(ScriptSectionType $scriptSectionType): void;

    /** {@inheritdoc} */
    public function remove(ScriptSectionType $scriptSectionType): void;
}