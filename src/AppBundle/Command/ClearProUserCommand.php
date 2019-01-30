<?php

namespace AppBundle\Command;


use AppBundle\Elasticsearch\Type\IndexableProUser;


class ClearProUserCommand extends AbstractClearCommand
{
    public function __construct()
    {
        parent::__construct('wamcar:clear:pro_user',
            'Clear the pro users (directory) from its index',
            IndexableProUser::TYPE);
    }
}