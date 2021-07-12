<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\VideoCoaching\VideoProjectMessage;
use Wamcar\VideoCoaching\VideoProjectMessageRepository;

class DoctrineVideoProjectMessageRepository extends EntityRepository implements VideoProjectMessageRepository
{
    use SoftDeletableEntityRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function add(VideoProjectMessage $videoProjectMessage): void
    {
        $this->_em->persist($videoProjectMessage);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(VideoProjectMessage $videoProjectMessage): void
    {
        $this->_em->merge($videoProjectMessage);
        $this->_em->persist($videoProjectMessage);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(VideoProjectMessage $videoProjectMessage): void
    {
        $this->_em->remove($videoProjectMessage);
        $this->_em->flush();
    }
}
