<?php


namespace AppBundle\Doctrine\Repository;


interface FooterLinkRepository
{
    /**
     * Finds all entities in the repository, ordered by ColumnNumber and position
     *
     * @return array The entities.
     */
    public function findAllOrdered();
}