<?php


namespace Wamcar\VideoCoaching;


use AppBundle\Doctrine\Repository\EntityRepository;

interface VideoProjectMessageRepository extends EntityRepository
{

    /** {@inheritdoc} */
    public function add(VideoProjectMessage $videoProjectMessage): void;

    /** {@inheritdoc} */
    public function update(VideoProjectMessage $videoProjectMessage): void;

    /** {@inheritdoc} */
    public function remove(VideoProjectMessage $videoProjectMessage): void;

    /**
     * @param VideoProject $videoProject
     * @param \DateTime|null $start Optionnal min of the interval
     * @param \DateTime|null $end Optionnal max of the interval
     * @return mixed
     */
    public function findByVideoProjectAndTimeInterval(VideoProject $videoProject, ?\DateTime $start, ?\DateTime $end);

}