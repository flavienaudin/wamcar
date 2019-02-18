<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Security\SecurityInterface\ApiUserProvider;
use AppBundle\Security\SecurityInterface\HasApiCredential;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;

class DoctrineGarageRepository extends EntityRepository implements GarageRepository, UserProviderInterface
{

    use SluggableEntityRepositoryTrait;

    /**
     * @param string $username
     * @return null|Garage
     */
    public function loadUserByUsername($username): ?Garage
    {
        return $this->findOneBy(['id' => $username]);
    }

    /**
     * @param UserInterface $garage
     * @return null|Garage
     */
    public function refreshUser(UserInterface $garage): ?Garage
    {
        return $this->loadUserByUsername($garage->getUsername());
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return Garage::class === $class;
    }
    /** Fin des méthodes du UserProviderInterface */


    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return parent::findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function findOne(int $garageId): ?Garage
    {
        return $this->findOneBy(['id' => $garageId]);
    }

    /**
     * {@inheritdoc}
     */
    public function add(Garage $garage)
    {
        $this->_em->persist($garage);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(Garage $garage): Garage
    {
        $this->_em->persist($garage);
        $this->_em->flush();

        return $garage;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Garage $garage)
    {
        $this->_em->remove($garage);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getLatest(): array
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function getByClientId(string $clientId): ?HasApiCredential
    {
        return $this->findOneBy(['apiClientId' => $clientId]);
    }
}
