<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\User\ProServiceCategory;
use Wamcar\User\ProServiceCategoryRepository;

class DoctrineProServiceCategoryRepository extends EntityRepository implements ProServiceCategoryRepository
{

    /**
     * {@inheritdoc}
     */
    public function remove(ProServiceCategory $proServiceCategory): void
    {
        $this->_em->remove($proServiceCategory);
        $this->_em->flush();
    }
}