<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\VideoCoaching\ScriptSequence;
use Wamcar\VideoCoaching\ScriptSequenceRepository;

class DoctrineScriptSequenceRepository extends EntityRepository implements ScriptSequenceRepository
{
    /**
     * {@inheritdoc}
     */
    public function add(ScriptSequence $scriptSequence): void
    {
        $this->_em->persist($scriptSequence);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(ScriptSequence $scriptSequence): void
    {
        $this->_em->merge($scriptSequence);
        $this->_em->persist($scriptSequence);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ScriptSequence $scriptSequence): void
    {
        $this->_em->remove($scriptSequence);
        $this->_em->flush();
    }

}