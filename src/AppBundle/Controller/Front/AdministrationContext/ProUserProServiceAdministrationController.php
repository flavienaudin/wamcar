<?php


namespace AppBundle\Controller\Front\AdministrationContext;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class ProUserProServiceAdministrationController extends BackendController
{

    protected function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManagerForClass($this->entity['class']);
        /* @var QueryBuilder */
        $queryBuilder = $em->createQueryBuilder()
            ->select('entity')
            ->from($this->entity['class'], 'entity')
            ->leftJoin('entity.proUser','proUser');

        if (!empty($dqlFilter)) {
            $queryBuilder->andWhere($dqlFilter);
        }

        if (null !== $sortField) {
            $queryBuilder->orderBy('entity.'.$sortField, $sortDirection ?: 'DESC');
        }

        return $queryBuilder;
    }

}