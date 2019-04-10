<?php

namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\Sale\Declaration;
use Wamcar\Sale\SaleDeclarationRepository;

class DoctrineSaleDeclarationRepository extends EntityRepository implements SaleDeclarationRepository
{
    use SoftDeletableEntityRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function add(Declaration $declaration): Declaration
    {
        $this->_em->persist($declaration);
        $this->_em->flush();
        return $declaration;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Declaration $declaration): Declaration
    {
        $this->_em->persist($declaration);
        $this->_em->flush();
        return $declaration;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Declaration $declaration)
    {
        $this->_em->remove($declaration);
        $this->_em->flush();
    }
}