<?php

namespace AppBundle\Doctrine\Type;


use Wamcar\User\Enum\FirstContactPreference;

class FirstContactPreferenceType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'first_contact_preference';
    /** @var string */
    protected $enumClass = FirstContactPreference::class;
}