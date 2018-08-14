<?php

namespace AppBundle\Elasticsearch\Builder;

use AppBundle\Elasticsearch\Type\IndexablePersonalProject;
use Wamcar\User\Project;

class IndexablePersonalProjectBuilder
{


    /**
     * IndexablePersonalProjectBuilder constructor.
     * To be defined as a service
     */
    public function __construct(){
    }

    /**
     * @param Project $project
     * @return IndexablePersonalProject
     */
    public function buildFromProject(Project $project): IndexablePersonalProject
    {
        return IndexablePersonalProject::createFromPersonalProject($project);
    }
}
