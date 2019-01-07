<?php

namespace AppBundle\Command;


use AppBundle\Elasticsearch\Type\IndexableProUser;


class ClearDirectoryCommand extends AbstractClearCommand
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('wamcar:directory:clear', 'Clear the directory', IndexableProUser::TYPE);
    }
}