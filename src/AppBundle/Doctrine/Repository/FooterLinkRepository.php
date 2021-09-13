<?php


namespace AppBundle\Doctrine\Repository;


use AppBundle\Doctrine\Entity\FooterLink;

interface FooterLinkRepository
{
    /**
     * Finds all entities in the repository, ordered by ColumnNumber and position
     *
     * @return array The entities.
     */
    public function findAllOrdered();

    /**
     * @param FooterLink $footerLink
     * @return boolean
     */
    public function remove(FooterLink $footerLink);
}