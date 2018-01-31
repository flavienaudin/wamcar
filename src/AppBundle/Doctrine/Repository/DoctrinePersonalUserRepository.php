<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Security\Repository\RegisteredWithConfirmationProvider;
use AppBundle\Security\Repository\UserWithResettablePasswordProvider;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wamcar\User\UserRepository;

class DoctrinePersonalUserRepository extends EntityRepository implements UserRepository, RegisteredWithConfirmationProvider, UserProviderInterface, UserWithResettablePasswordProvider
{
    use DoctrineUserRepositoryTrait;
    use PasswordResettableRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function findOneByRegistrationToken($registrationToken): ApplicationUser
    {
        return $this->findOneBy(['registrationToken' => $registrationToken]);
    }

    /**
     * @return array
     */
    public function retrieveUserToRemindToAddPicture()
    {
        $qb = $this->createQueryBuilder('u');
        $qb
            ->addSelect('pv')
            ->leftJoin('u.vehicles', 'pv', 'WITH', 'pv.deletedAt is NULL')
            ->leftJoin('pv.pictures', 'vp')
            ->where($qb->expr()->andX(
                $qb->expr()->gte('u.createdAt', ':select_interval_start'),
                $qb->expr()->lt('u.createdAt', ':select_interval_end')
            ))
            ->groupBy('u.id, pv.id')
            ->having('count(vp.id) < 2');

        $selectIntervalStart = new \DateTime("now");
        $selectIntervalStart->sub(new \DateInterval('PT25H'));
        $qb->setParameter("select_interval_start", $selectIntervalStart);

        $selectIntervalEnd = new \DateTime("now");
        $selectIntervalEnd->sub(new \DateInterval('PT24H'));
        $qb->setParameter("select_interval_end", $selectIntervalEnd);

        return $qb->getQuery()->getResult();
    }

}
