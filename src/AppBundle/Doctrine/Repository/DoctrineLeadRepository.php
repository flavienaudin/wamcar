<?php

namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\User\Lead;
use Wamcar\User\LeadRepository;

class DoctrineLeadRepository extends EntityRepository implements LeadRepository
{

    /**
     * {@inheritdoc}
     */
    public function add(Lead $lead): Lead
    {
        $this->_em->persist($lead);
        $this->_em->flush();
        return $lead;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Lead $lead): Lead
    {
        $this->_em->persist($lead);
        $this->_em->flush();
        return $lead;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Lead $lead)
    {
        $this->_em->remove($lead);
        $this->_em->flush();
    }
}