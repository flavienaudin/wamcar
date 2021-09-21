<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\VideoCoaching\ScriptVersion;
use Wamcar\VideoCoaching\ScriptVersionRepository;

class DoctrineScriptVersionRepository extends EntityRepository implements ScriptVersionRepository
{
    use SluggableEntityRepositoryTrait;
    use SoftDeletableEntityRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function add(ScriptVersion $scriptVersion): void
    {
        $this->_em->persist($scriptVersion);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(ScriptVersion $scriptVersion): void
    {
        $this->_em->merge($scriptVersion);
        $this->_em->persist($scriptVersion);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ScriptVersion $scriptVersion): void
    {
        $this->_em->remove($scriptVersion);
        $this->_em->flush();
    }

}