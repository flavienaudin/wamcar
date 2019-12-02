<?php

namespace AppBundle\Doctrine\Repository;

use AppBundle\Security\Repository\UserWithResettablePasswordProvider;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wamcar\User\PersonalUser;
use Wamcar\User\UserRepository;
use Wamcar\Vehicle\Enum\LeadCriteriaSelection;
use Wamcar\Vehicle\PersonalVehicle;

class DoctrineProUserRepository extends EntityRepository implements UserRepository, UserProviderInterface, UserWithResettablePasswordProvider
{
    use DoctrineUserRepositoryTrait;
    use SoftDeletableEntityRepositoryTrait;
    use PasswordResettableRepositoryTrait;
    use SluggableEntityRepositoryTrait;

    /**
     * Find ProUsers to display on the homepage
     */
    public function findProUsersForHomepage(): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where($qb->expr()->isNotNull('u.landingPosition'))
            ->orderBy('u.landingPosition', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param PersonalUser $personalUser
     * @return array
     */
    public function findInterestedByPersonalUser(PersonalUser $personalUser): array
    {
        $mainQueryQb = $this->createQueryBuilder('pro')
            ->join('pro.preferences', 'up');

        if ($personalUser->getCity() != null) {
            $subqueryExistsGarageNearPersoanlUser = $this->getEntityManager()->createQueryBuilder();
            $subqueryExistsGarageNearPersoanlUser
                ->select('1')
                ->from('Wamcar:Garage\GarageProUser', 'gpu')
                ->from('Wamcar:User\PersonalUser', 'cu')// Current PersonalUser
                ->join('gpu.garage', 'g')
                ->where($subqueryExistsGarageNearPersoanlUser->expr()->eq('gpu.proUser', 'pro'))
                ->andWhere($subqueryExistsGarageNearPersoanlUser->expr()->isNull('gpu.requestedAt'))
                ->andWhere($subqueryExistsGarageNearPersoanlUser->expr()->eq('cu', ':personalUser'))
                ->andWhere($subqueryExistsGarageNearPersoanlUser->expr()->lte(
                    "6372.8 * 2 * asin(sqrt(power( sin(radians(cu.userProfile.city.latitude - g.address.city.latitude)/2),2) +
                        power( sin(radians(cu.userProfile.city.longitude - g.address.city.longitude)/2), 2) * cos(radians(g.address.city.latitude)) * cos(radians(cu.userProfile.city.latitude))))",
                    'up.leadLocalizationRadiusCriteria'
                ));
            $mainQueryQb->setParameter('personalUser', $personalUser);

            // ProUser localization => PersonalUser is inside ProUser's garages' position +/- ProUser's preference Radius
            $mainQueryQb->where($mainQueryQb->expr()->exists($subqueryExistsGarageNearPersoanlUser->getDQL()));
        }

        $whereClause = null;
        $personalUserHasPartExchange = count($personalUser->getVehicles()) > 0;
        $personalUserHasProject = $personalUser->getProject() != null && !$personalUser->getProject()->isEmpty();

        if ($personalUserHasPartExchange && $personalUserHasProject) {
            // Achat et reprise
            $whereClause = $mainQueryQb->expr()->eq('up.leadProjectWithPartExchange', $mainQueryQb->expr()->literal(true));
        } elseif ($personalUserHasPartExchange && !$personalUserHasProject) {
            // Reprise sêche
            $whereClause = $mainQueryQb->expr()->eq('up.leadOnlyPartExchange', $mainQueryQb->expr()->literal(true));
        } elseif (!$personalUserHasPartExchange && $personalUserHasProject) {
            // Achat
            $whereClause = $mainQueryQb->expr()->eq('up.leadOnlyProject', $mainQueryQb->expr()->literal(true));
        }
        if ($whereClause != null) {
            $mainQueryQb->andWhere($whereClause);
        }

        if ($personalUserHasPartExchange) {
            $minKm = PHP_INT_MAX;
            /** @var PersonalVehicle $vehicle */
            foreach ($personalUser->getVehicles() as $vehicle) {
                $minKm = min($vehicle->getMileage(), $minKm);
            }
            $mainQueryQb->andWhere(
                $mainQueryQb->expr()->orX(
                    $mainQueryQb->expr()->isNull('up.leadPartExchangeKmMaxCriteria'),
                    $mainQueryQb->expr()->gte('up.leadPartExchangeKmMaxCriteria', ':kmMinPersonalVehicle')
                )
            );
            $mainQueryQb->setParameter('kmMinPersonalVehicle', $minKm);
        }

        if ($personalUserHasProject) {
            $mainQueryQb->andWhere(
                $mainQueryQb->expr()->orX(
                    $mainQueryQb->expr()->isNull('up.leadProjectBudgetMinCriteria'),
                    $mainQueryQb->expr()->lte('up.leadProjectBudgetMinCriteria', ':budgetMaxPersonalVehicle')
                )
            );
            $mainQueryQb->setParameter('budgetMaxPersonalVehicle', $personalUser->getProject()->getBudget());
        }

        return $mainQueryQb->getQuery()->execute();
    }

    /**
     * Admin filter: don't display deleted ProUSer.
     */
    public static function adminQueryBuilderToSelectCategoryForProUser(EntityRepository $r): QueryBuilder
    {
        $qb = $r->createQueryBuilder('u');
        return $qb
            ->where($qb->expr()->isNull('u.deletedAt'))
            ->orderBy('u.userProfile.firstName', 'ASC')
            ->addOrderBy('u.userProfile.lastName', 'ASC');
    }
}
