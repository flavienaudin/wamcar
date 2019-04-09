<?php

namespace Wamcar\User;


interface LeadRepository
{

    /**
     * Retrieve the Leads of the $proUser : In ProUser's conversations and $proUser's VehicleLikes
     * @param ProUser $proUser
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getPotentialLeadsByProUser(ProUser $proUser): array;

    /**
     * Retrieve the leads of the $proUser filtered and ordered by the $params
     * @param ProUser $proUser
     * @param array $params Request's params
     * @return array
     */
    public function getLeadsByRequest(ProUser $proUser, array $params): array;

    /**
     * Finds a single entity by a set of criteria.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     *
     * @return object|null The entity instance or NULL if the entity can not be found.
     */
    public function findOneBy(array $criteria, array $orderBy = null);

    /**
     * @param Lead $lead
     * @return Lead
     */
    public function add(Lead $lead): Lead;

    /**
     * @param Lead $lead
     * @return Lead
     */
    public function update(Lead $lead): Lead;

    /**
     * @param Lead $lead
     * @return boolean
     */
    public function remove(Lead $lead);

}