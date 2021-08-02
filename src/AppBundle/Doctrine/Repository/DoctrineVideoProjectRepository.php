<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectRepository;

class DoctrineVideoProjectRepository extends EntityRepository implements VideoProjectRepository
{
    use SluggableEntityRepositoryTrait;
    use SoftDeletableEntityRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function add(VideoProject $videoProject): void
    {
        $this->_em->persist($videoProject);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(VideoProject $videoProject): void
    {
        $this->_em->merge($videoProject);
        $this->_em->persist($videoProject);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(VideoProject $videoProject): void
    {
        $this->_em->remove($videoProject);
        $this->_em->flush();
    }
}
