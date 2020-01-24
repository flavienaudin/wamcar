<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\User\VideosInsertReposistory;

class DoctrineVideosInsertRepository extends EntityRepository implements VideosInsertReposistory
{

}