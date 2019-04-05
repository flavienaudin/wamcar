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