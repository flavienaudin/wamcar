<?php


namespace AppBundle\Controller\Front\AdministrationContext;


class ProUserProServiceAdministrationController extends BackendController
{

    protected function createSearchQueryBuilder($entityClass, $searchQuery, array $searchableFields, $sortField = null, $sortDirection = null, $dqlFilter = null)
    {
        $queryBuilder = parent::createSearchQueryBuilder($entityClass, $searchQuery, $searchableFields, $sortField, $sortDirection, $dqlFilter);
        $queryBuilder->leftJoin('entity.proUser', 'proUser');
        $queryBuilder->leftJoin('entity.proService', 'proService');

        if (!empty($searchQuery)) {
            $lowerSearchQuery = mb_strtolower($searchQuery);
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like($queryBuilder->expr()->lower('proUser.userProfile.firstName'), ':fuzzy_query'),
                    $queryBuilder->expr()->like($queryBuilder->expr()->lower('proUser.userProfile.lastName'), ':fuzzy_query'),
                    $queryBuilder->expr()->in($queryBuilder->expr()->lower('proUser.userProfile.firstName'), ':words_query'),
                    $queryBuilder->expr()->in($queryBuilder->expr()->lower('proUser.userProfile.lastName'), ':words_query'),
                    $queryBuilder->expr()->like($queryBuilder->expr()->lower('proService.name'), ':fuzzy_query'),
                    $queryBuilder->expr()->in($queryBuilder->expr()->lower('proService.name'), ':words_query')
                )
            );
            $queryBuilder->setParameter('fuzzy_query', '%' . $lowerSearchQuery . '%');
            $queryBuilder->setParameter('words_query', explode(' ', $lowerSearchQuery));
        }
        return $queryBuilder;
    }

    protected function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        $queryBuilder = parent::createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);
        $queryBuilder->leftJoin('entity.proUser', 'proUser');
        return $queryBuilder;
    }


}