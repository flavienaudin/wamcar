<?php

namespace AppBundle\Doctrine\Type;


use Wamcar\User\Enum\PersonalOrientationChoices;

final class PersonalOrientationType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'personal_orientation';
    /** @var string */
    protected $enumClass = PersonalOrientationChoices::class;
}
