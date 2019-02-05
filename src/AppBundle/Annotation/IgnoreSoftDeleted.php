<?php

namespace AppBundle\Annotation;


use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 */
class IgnoreSoftDeleted extends Annotation
{
}