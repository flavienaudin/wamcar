<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\User\Project;
use Wamcar\User\ProjectRepository;

class DoctrineProjectRepository extends EntityRepository implements ProjectRepository
{

    /**
     * {@inheritdoc}
     */
    public function update(Project $project): void
    {
        $this->_em->persist($project);
        $this->_em->flush();
    }
}
