<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\User\ProService;
use Wamcar\User\ProServiceCategoryRepository;

class DoctrineProServiceCategoryRepository extends EntityRepository implements ProServiceCategoryRepository
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