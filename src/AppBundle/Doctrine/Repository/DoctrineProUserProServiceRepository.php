<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\User\ProUserProService;
use Wamcar\User\ProUserProServiceRepository;

class DoctrineProUserProServiceRepository extends EntityRepository implements ProUserProServiceRepository
{

    /**
     * {@inheritdoc}
     */
    public function remove(ProUserProService $proUserProService): void
    {
        $this->_em->remove($proUserProService);
        $this->_em->flush();
    }

    /**
     * @inheritDoc
     */
    public function removeBulk(array $proUserProServices, ?int $batchSize = 50)
    {
        $idx = 0;
        /** @var ProUserProService $proUserProService */
        foreach ($proUserProServices as $proUserProService) {
            $idx++;
            $this->_em->remove($proUserProService);
            if (($idx % $batchSize) === 0) {
                $this->_em->flush();
            }
        }
        $this->_em->flush();
    }
}