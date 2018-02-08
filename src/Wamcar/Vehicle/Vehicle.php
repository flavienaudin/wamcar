<?php

namespace Wamcar\Vehicle;

use Wamcar\User\BaseUser;

interface Vehicle
{

    /**
     * @param BaseUser|null $user
     * @return bool
     */
    public function canEditMe(BaseUser $user = null): bool;

}
