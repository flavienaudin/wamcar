<?php

namespace AppBundle\Services\User;

use AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository;
use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Security\Repository\UserWithResettablePasswordProvider;


class UserGlobalSearchService
{
    /** @var DoctrinePersonalUserRepository */
    private $personalUserRepository;

    /** @var DoctrineProUserRepository */
    private $proUserRepository;

    /**
     * UserGlobalSearchService constructor.
     * @param DoctrinePersonalUserRepository $personalUserRepository
     * @param DoctrineProUserRepository $proUserRepository
     */
    public function __construct(
        DoctrinePersonalUserRepository $personalUserRepository,
        DoctrineProUserRepository $proUserRepository
    )
    {
        $this->personalUserRepository = $personalUserRepository;
        $this->proUserRepository = $proUserRepository;
    }

    /**
     * @param string $passwordResetToken
     * @return \AppBundle\Security\HasPasswordResettable|null
     * @throws \Exception
     */
    public function findOneByPasswordResetToken(string $passwordResetToken)
    {
        if (!$this->personalUserRepository instanceof UserWithResettablePasswordProvider && !$this->proUserRepository instanceof UserWithResettablePasswordProvider) {
            throw new \Exception('UserRepository must implement "UserWithResettablePasswordProvider" to be able to reset password');
        }

        if ($personalUser = $this->personalUserRepository->findOneByPasswordResetToken($passwordResetToken)) {
            return $personalUser;
        } elseif ($proUser = $this->proUserRepository->findOneByPasswordResetToken($passwordResetToken)) {
            return $proUser;
        } else {
            return null;
        }
    }


    public function findNewPersonalUser(int $since): array
    {
        $selectIntervalEnd = new \DateTime("now");
        $selectIntervalEnd->sub(new \DateInterval('PT1H'));
        $selectIntervalStart = clone $selectIntervalEnd;
        $selectIntervalStart->sub(new \DateInterval('PT' . $since . 'H'));

        $mainQb = $this->personalUserRepository->createQueryBuilder('u');

        $subQueryExistsPersonalVehicleQb = $mainQb->getEntityManager()->createQueryBuilder();
        $subQueryExistsPersonalVehicleQb
            ->select('1')->from('Wamcar:Vehicle\PersonalVehicle', 'pv')
            ->where($subQueryExistsPersonalVehicleQb->expr()->isNull('pv.deletedAt'))
            ->andWhere($subQueryExistsPersonalVehicleQb->expr()->eq('pv.owner', 'u'));

        $subQueryExistsProjectVehicleQb = $mainQb->getEntityManager()->createQueryBuilder();
        $subQueryExistsProjectVehicleQb->select('1')
            ->from('Wamcar:User\ProjectVehicle', 'prv')
            ->where($subQueryExistsProjectVehicleQb->expr()->eq('prv.project', 'pr'));

        $subQueryExistsNonEmptyProjectQb = $mainQb->getEntityManager()->createQueryBuilder();
        $subQueryExistsNonEmptyProjectQb->select('1')
            ->from('Wamcar:User\Project', 'pr')
            ->where($subQueryExistsNonEmptyProjectQb->expr()->eq('pr.personalUser', 'u'))
            ->andWhere($subQueryExistsNonEmptyProjectQb->expr()->isNull('pr.deletedAt'))
            ->andWhere(
                $subQueryExistsNonEmptyProjectQb->expr()->orX(
                    $subQueryExistsNonEmptyProjectQb->expr()->andX(
                        $subQueryExistsNonEmptyProjectQb->expr()->isNotNull('pr.budget'),
                        $subQueryExistsNonEmptyProjectQb->expr()->gt('pr.budget', 0)
                    ),
                    $subQueryExistsNonEmptyProjectQb->expr()->andX(
                        $subQueryExistsNonEmptyProjectQb->expr()->isNotNull('pr.description'),
                        $subQueryExistsNonEmptyProjectQb->expr()->neq('pr.description', '\'\'')
                    ),
                    $subQueryExistsNonEmptyProjectQb->expr()->exists(
                        $subQueryExistsProjectVehicleQb->getDQL()
                    )
                )
            );
        $mainQb->where($mainQb->expr()->between('u.createdAt', ':afterDate', ':beforeDate'))
            ->andWhere(
                $mainQb->expr()->orX(
                    $mainQb->expr()->exists($subQueryExistsPersonalVehicleQb->getDQL()),
                    $mainQb->expr()->exists($subQueryExistsNonEmptyProjectQb->getDQL())
                )
            );
        $mainQb->setParameter(':afterDate', $selectIntervalStart)
            ->setParameter(':beforeDate', $selectIntervalEnd);
        return $mainQb->getQuery()->execute();
    }

}
