<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\VideoCoaching\ScriptSectionType;
use Wamcar\VideoCoaching\ScriptSectionTypeRepository;

class DoctrineScriptSectionTypeRepository extends EntityRepository implements ScriptSectionTypeRepository
{
    /**
     * {@inheritdoc}
     */
    public function add(ScriptSectionType $scriptSectionType): void
    {
        $this->_em->persist($scriptSectionType);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(ScriptSectionType $scriptSectionType): void
    {
        $this->_em->merge($scriptSectionType);
        $this->_em->persist($scriptSectionType);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ScriptSectionType $scriptSectionType): void
    {
        $this->_em->remove($scriptSectionType);
        $this->_em->flush();
    }
}