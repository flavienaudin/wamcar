<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\User\ProUserProService;
use Wamcar\User\ProUserProServiceRepository;

class DoctrineProUserProServiceRepository extends EntityRepository implements ProUserProServiceRepository
{

    /**
     * {@inheritdoc}
     */
    public function remove(ProUserProService $proUserProService): void
    {
        $this->_em->remove($proUserProService);
        $this->_em->flush();
    }
}