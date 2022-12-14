<?php

namespace AppBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Wamcar\Garage\Garage;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\Vehicle;
use Wamcar\Vehicle\VehicleRepository;

class DoctrineVehicleRepository extends EntityRepository implements VehicleRepository
{
    use SluggableEntityRepositoryTrait;
    use SoftDeletableEntityRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function add(Vehicle $vehicle): void
    {
        $this->_em->persist($vehicle);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(Vehicle $vehicle): void
    {
        $this->_em->merge($vehicle);
        $this->_em->persist($vehicle);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function saveBulk(array $vehicles, ?int $batchSize = 50)
    {
        $idx = 0;
        /** @var BaseVehicle $vehicle */
        foreach ($vehicles as $vehicle) {
            $idx++;
            $this->_em->persist($vehicle);
            if (($idx % $batchSize) === 0) {
                $this->_em->flush();
            }
        }
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Vehicle $vehicle): void
    {
        $this->_em->remove($vehicle);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function removeBulk(array $vehicles, ?int $batchSize = 50)
    {
        $idx = 0;
        /** @var BaseVehicle $vehicle */
        foreach ($vehicles as $vehicle) {
            $idx++;
            $this->_em->remove($vehicle);
            if (($idx % $batchSize) === 0) {
                $this->_em->flush();
            }
        }
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForGarage(Garage $garage): array
    {
        return $this->findBy(['garage' => $garage]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $ids): array
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('p')
            ->from($this->getClassName(), 'p')
            ->where($qb->expr()->in('p.id', $ids))
            ->orderBy($qb->expr()->asc('FIELD(p.id, :orderedIds) '));
        $qb->setParameter('orderedIds', $ids);
        return $qb->getQuery()->getResult();
    }
}
