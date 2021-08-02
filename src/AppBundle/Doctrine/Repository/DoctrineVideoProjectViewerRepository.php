<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\VideoCoaching\VideoProjectViewer;
use Wamcar\VideoCoaching\VideoProjectViewerRepository;

class DoctrineVideoProjectViewerRepository extends EntityRepository implements VideoProjectViewerRepository
{

    /**
     * {@inheritdoc}
     */
    public function add(VideoProjectViewer $videoProjectViewer)
    {
        $this->_em->persist($videoProjectViewer);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(VideoProjectViewer $videoProjectViewer)
    {
        $videoProjectViewer = $this->_em->merge($videoProjectViewer);
        $this->_em->persist($videoProjectViewer);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(VideoProjectViewer $videoProjectViewer)
    {
        $this->_em->remove($videoProjectViewer);
        $this->_em->flush();
    }
}