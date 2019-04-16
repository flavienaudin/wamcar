<?php

namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\Sale\Declaration;
use Wamcar\Sale\SaleDeclarationRepository;
use Wamcar\User\ProUser;

class DoctrineSaleDeclarationRepository extends EntityRepository implements SaleDeclarationRepository
{
    use SoftDeletableEntityRepositoryTrait;


    /**
     * @inheritDoc
     */
    public function getCountSales(ProUser $proUser, ?int $sinceDays = 30, ?\DateTimeInterface $referenceDate = null): int
    {
        if (empty($referenceDate)) {
            $referenceDate = new \DateTime();
        }
        $firstDate = clone $referenceDate;
        $firstDate->sub(new \DateInterval('P' . $sinceDays . 'D'));

        $qb = $this->createQueryBuilder('s');
        $qb->select('COUNT(s.id)')
            ->where($qb->expr()->eq('s.proUserSeller', ':proUser'))
            ->andWhere($qb->expr()->isNotNull('s.transactionSaleAmount'))
            ->andWhere('s.updatedAt >= :firstDate')
            ->andWhere('s.updatedAt < :referenceDate')
            ->setParameter('proUser', $proUser)
            ->setParameter('firstDate', $firstDate)
            ->setParameter('referenceDate', $referenceDate);
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @inheritDoc
     */
    public function getCountPartExchanges(ProUser $proUser, ?int $sinceDays = 30, ?\DateTimeInterface $referenceDate = null): int
    {
        if (empty($referenceDate)) {
            $referenceDate = new \DateTime();
        }
        $firstDate = clone $referenceDate;
        $firstDate->sub(new \DateInterval('P' . $sinceDays . 'D'));

        $qb = $this->createQueryBuilder('s');
        $qb->select('COUNT(s.id)')
            ->where($qb->expr()->eq('s.proUserSeller', ':proUser'))
            ->andWhere($qb->expr()->isNotNull('s.transactionPartExchangeAmount'))
            ->andWhere('s.updatedAt >= :firstDate')
            ->andWhere('s.updatedAt < :referenceDate')
            ->setParameter('proUser', $proUser)
            ->setParameter('firstDate', $firstDate)
            ->setParameter('referenceDate', $referenceDate);
        return $qb->getQuery()->getSingleScalarResult();

    }

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