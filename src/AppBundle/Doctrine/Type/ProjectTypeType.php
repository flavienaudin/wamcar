<?php

namespace AppBundle\Doctrine\Type;

use Wamcar\User\ProjectType;

final class ProjectTypeType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'project_type';
    /** @var string */
    protected $enumClass = ProjectType::class;
}
