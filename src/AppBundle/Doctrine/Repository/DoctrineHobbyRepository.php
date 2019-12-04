<?php


namespace AppBundle\Doctrine\Repository;


use Doctrine\ORM\EntityRepository;
use Wamcar\User\HobbyRepository;

class DoctrineHobbyRepository extends EntityRepository implements HobbyRepository
{

}