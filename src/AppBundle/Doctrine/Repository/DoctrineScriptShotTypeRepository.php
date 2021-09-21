<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\VideoCoaching\ScriptShotType;
use Wamcar\VideoCoaching\ScriptShotTypeRepository;

class DoctrineScriptShotTypeRepository extends EntityRepository implements ScriptShotTypeRepository
{
    /**
     * {@inheritdoc}
     */
    public function add(ScriptShotType $scriptShotType): void
    {
        $this->_em->persist($scriptShotType);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(ScriptShotType $scriptShotType): void
    {
        $this->_em->merge($scriptShotType);
        $this->_em->persist($scriptShotType);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ScriptShotType $scriptShotType): void
    {
        $this->_em->remove($scriptShotType);
        $this->_em->flush();
    }
}