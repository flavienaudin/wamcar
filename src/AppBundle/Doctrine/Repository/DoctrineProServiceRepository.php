<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\User\ProService;
use Wamcar\User\ProServiceRepository;

class DoctrineProServiceRepository extends EntityRepository implements ProServiceRepository
{

    /**
     * {@inheritdoc}
     */
    public function remove(ProService $proService): void
    {
        $this->_em->remove($proService);
        $this->_em->flush();
    }
}