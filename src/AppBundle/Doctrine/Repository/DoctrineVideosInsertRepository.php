<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\User\VideosInsert;
use Wamcar\User\VideosInsertReposistory;

class DoctrineVideosInsertRepository extends EntityRepository implements VideosInsertReposistory
{


    /**
     * {@inheritdoc}
     */
    public function add(VideosInsert $videosInsert): VideosInsert
    {
        $this->_em->persist($videosInsert);
        $this->_em->flush();
        return $videosInsert;
    }

    /**
     * {@inheritdoc}
     */
    public function update(VideosInsert $videosInsert): VideosInsert
    {
        $this->_em->persist($videosInsert);
        $this->_em->flush();
        return $videosInsert;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(VideosInsert $videosInsert): void
    {
        $this->_em->remove($videosInsert);
        $this->_em->flush();
    }
}