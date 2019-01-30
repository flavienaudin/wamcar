<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexablePersonalProject;

class ClearPersonalProjectCommand extends AbstractClearCommand
{
    public function __construct()
    {
        parent::__construct('wamcar:clear:personal_project',
            'Clear all personal projects from its index',
            IndexablePersonalProject::TYPE);
    }
}
