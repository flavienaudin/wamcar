<?php


namespace AppBundle\Doctrine\Repository;


use AppBundle\Doctrine\Entity\FooterLink;
use Doctrine\ORM\EntityRepository;

class DoctrineFooterLinkRepository extends EntityRepository implements FooterLinkRepository
{
    /**
     * @inheritDoc
     */
    public function findAllOrdered()
    {
        return $this->findBy([], ['columnNumber' => 'ASC', 'position' => 'ASC']);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(FooterLink $footerLink): void
    {
        $this->_em->remove($footerLink);
        $this->_em->flush();
    }
}