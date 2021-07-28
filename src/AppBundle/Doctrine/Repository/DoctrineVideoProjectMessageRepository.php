<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectMessage;
use Wamcar\VideoCoaching\VideoProjectMessageRepository;

class DoctrineVideoProjectMessageRepository extends EntityRepository implements VideoProjectMessageRepository
{
    use SoftDeletableEntityRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function add(VideoProjectMessage $videoProjectMessage): void
    {
        $this->_em->persist($videoProjectMessage);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(VideoProjectMessage $videoProjectMessage): void
    {
        $this->_em->merge($videoProjectMessage);
        $this->_em->persist($videoProjectMessage);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove(VideoProjectMessage $videoProjectMessage): void
    {
        $this->_em->remove($videoProjectMessage);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findByVideoProjectAndTimeInterval(VideoProject $videoProject, ?\DateTime $start, ?\DateTime $end)
    {
        $qb = $this->createQueryBuilder('m');
        $qb->where($qb->expr()->eq('m.videoProject', ':videoProjectId'));
        if ($start) {
            $qb->andWhere($qb->expr()->gte('m.createdAt', ':startDate'))
                ->setParameter(':startDate', $start);;
        }
        if ($end) {
            $qb->andWhere($qb->expr()->lt('m.createdAt', ':endDate'))
                ->setParameter(':endDate', $end);
        }
        $qb->setParameter(':videoProjectId', $videoProject->getId());
        $qb->orderBy("m.createdAt", "DESC");
        return $qb->getQuery()->getResult();
    }
}
