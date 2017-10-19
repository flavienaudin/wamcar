<?php

namespace AppBundle\Doctrine\Type;

use Wamcar\User\Title;

final class UserTitleType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'user_title_state';
    /** @var string */
    protected $enumClass = Title::class;
}
