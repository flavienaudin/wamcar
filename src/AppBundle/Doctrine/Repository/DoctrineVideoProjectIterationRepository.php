<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\VideoCoaching\VideoProjectIteration;
use Wamcar\VideoCoaching\VideoProjectIterationRepository;

class DoctrineVideoProjectIterationRepository extends EntityRepository implements VideoProjectIterationRepository
{
    use SluggableEntityRepositoryTrait;
    use SoftDeletableEntityRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function add(VideoProjectIteration $videoProjectIteration): void
    {
        $this->_em->persist($videoProjectIteration);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(VideoProjectIteration $videoProjectIteration): void
    {
        $this->_em->merge($videoProjectIteration);
        $this->_em->persist($videoProjectIteration);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(VideoProjectIteration $videoProjectIteration): void
    {
        $this->_em->remove($videoProjectIteration);
        $this->_em->flush();
    }
}
