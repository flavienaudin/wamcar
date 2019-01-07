<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexablePersonalProject;

class ClearPersonalProjectCommand extends AbstractClearCommand
{

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct('wamcar:clear:personal_project',
            'Clear all personal projects from the index',
            IndexablePersonalProject::TYPE);
    }
}
