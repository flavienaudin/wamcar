<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\Conversation\ProContactMessage;
use Wamcar\Conversation\ProContactMessageRepository;

class DoctrineProContactMessageRepository extends EntityRepository implements ProContactMessageRepository
{

    /**
     * {@inheritdoc}
     */
    public function add(ProContactMessage $proContactMessage)
    {
        $this->_em->persist($proContactMessage);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(ProContactMessage $proContactMessage)
    {
        $affinityDegree = $this->_em->merge($proContactMessage);
        $this->_em->persist($affinityDegree);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ProContactMessage $proContactMessage)
    {
        $this->_em->remove($proContactMessage);
        $this->_em->flush();
    }
}