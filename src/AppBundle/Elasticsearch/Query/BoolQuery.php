<?php

namespace AppBundle\Elasticsearch\Query;


use Novaway\ElasticsearchClient\Query\BoolQuery as BaseBoolQuery;

class BoolQuery extends BaseBoolQuery
{

    /** @var int|null $minimum_should_match */
    private $minimum_should_match;
    /**
     * @var bool|null $disable_coord Disabled or not the query coordination factor :
     * The coordination factor (coord) is used to reward documents that contain a higher percentage of the query terms
     */
    private $disable_coord;

    /**
     * BoolQuery constructor.
     * @param null|int $minimum_should_match
     * @param null|bool $disable_coord
     */
    public function __construct(?int $minimum_should_match = null, ?bool $disable_coord = false)
    {
        parent::__construct();
        $this->minimum_should_match = $minimum_should_match;
        $this->disable_coord = $disable_coord;
    }

    public function formatForQuery(): array
    {
        $formatedQuery = parent::formatForQuery();

        if ($this->minimum_should_match) {
            $formatedQuery['bool']['minimum_should_match'] = $this->minimum_should_match;

        }
        $formatedQuery['bool']['disable_coord'] = $this->disable_coord;

        return $formatedQuery;
    }


}