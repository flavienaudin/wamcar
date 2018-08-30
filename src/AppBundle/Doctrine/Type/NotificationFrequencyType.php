<?php

namespace AppBundle\Doctrine\Type;


use Wamcar\Vehicle\Enum\NotificationFrequency;

final class NotificationFrequencyType extends BaseEnumType
{
    /** @var string */
    protected $typeName = 'notification_frequency';
    /** @var string */
    protected $enumClass = NotificationFrequency::class;
}
