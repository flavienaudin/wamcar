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
     * Finds an entity by its primary key / identifier.
     *
     * @param mixed    $id          The identifier.
     * @return object|null The entity instance or NULL if the entity can not be found.
     */
    public function find($id);

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
     * Compte les leads contacté pendant l'intervalle de la référence
     * @param BaseUser $user
     * @param int|null $sinceDays Intervalle de temps avant la date de référence
     * @param \DateTimeInterface|null $referenceDate Date de référence (par défaut le jour même)
     * @return int
     */
    public function getCountLeadsByLastDateOfContact(BaseUser $user, ?int $sinceDays = 30, ?\DateTimeInterface $referenceDate = null): int;
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