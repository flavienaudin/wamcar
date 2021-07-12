<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\VideoCoaching\VideoVersion;
use Wamcar\VideoCoaching\VideoVersionRepository;

class DoctrineVideoVersionRepository extends EntityRepository implements VideoVersionRepository
{
    use SoftDeletableEntityRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function add(VideoVersion $videoVersion): void
    {
        $this->_em->persist($videoVersion);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(VideoVersion $videoVersion): void
    {
        $this->_em->merge($videoVersion);
        $this->_em->persist($videoVersion);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(VideoVersion $videoVersion): void
    {
        $this->_em->remove($videoVersion);
        $this->_em->flush();
    }
}
