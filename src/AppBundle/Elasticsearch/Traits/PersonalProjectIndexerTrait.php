<?php

namespace AppBundle\Elasticsearch\Traits;

use AppBundle\Elasticsearch\Builder\IndexablePersonalProjectBuilder;
use AppBundle\Elasticsearch\Type\IndexablePersonalProject;
use Novaway\ElasticsearchClient\ObjectIndexer;
use Wamcar\User\Project;

trait PersonalProjectIndexerTrait
{
    /** @var ObjectIndexer */
    private $objectIndexer;
    /** @var IndexablePersonalProjectBuilder */
    private $indexablePersonalProjectBuilder;

    protected function indexPersonalProject(Project $project)
    {
        $indexablePersonalProject = $this->indexablePersonalProjectBuilder->buildFromProject($project);
        if ($indexablePersonalProject->shouldBeIndexed()) {
            $this->objectIndexer->index($indexablePersonalProject, IndexablePersonalProject::TYPE);
        } else {
            $this->objectIndexer->remove($indexablePersonalProject, IndexablePersonalProject::TYPE);
        }
    }
}
