<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;

class DoctrineFooterLinkRepository extends EntityRepository implements FooterLinkRepository
{
    /**
     * @inheritDoc
     */
    public function findAllOrdered(){
        return $this->findBy([], ['columnNumber' => 'ASC', 'position' => 'ASC']);
    }
}