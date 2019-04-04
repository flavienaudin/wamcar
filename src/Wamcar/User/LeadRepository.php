<?php

namespace Wamcar\User;


interface LeadRepository
{

    /**
     * @param Lead $lead
     *
     * @return Lead
     */
    public function add(Lead $lead): Lead;

    /**
     * @param Lead $lead
     *
     * @return Lead
     */
    public function update(Lead $lead): Lead;

    /**
     * @param Lead $lead
     *
     * @return boolean
     */
    public function remove(Lead $lead);

}