<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\IndexableSearchItem;

class ClearSearchItemCommand extends AbstractClearCommand
{
    public function __construct()
    {
        parent::__construct('wamcar:clear:search_item',
            'Clear all search item from its index',
            IndexableSearchItem::TYPE);
    }
}
