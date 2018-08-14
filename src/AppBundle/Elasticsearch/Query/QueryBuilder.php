<?php

namespace AppBundle\Elasticsearch\Query;

use Novaway\ElasticsearchClient\Query\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{

    // Multiply the _score with the function result (default)
    const MULTIPLY = "multiply";
    // Add the function result to the _score
    const SUM = "sum";
    // The lower of the _score and the function result
    const MIN = "min";
    // The higher of the _score and the function result
    const MAX = "max";
    // Replace the _score with the function result
    const REPLACE = "replace";

    /** @var string $functionScoreBoostMode */
    protected $functionScoreBoostMode;

    public function __construct(int $offset = BaseQueryBuilder::DEFAULT_OFFSET, int $limit = BaseQueryBuilder::DEFAULT_LIMIT, float $minScore = BaseQueryBuilder::DEFAULT_MIN_SCORE, string $boostMode = self::MULTIPLY)
    {
        BaseQueryBuilder::__construct($offset, $limit, $minScore);
        $this->functionScoreBoostMode = $boostMode;
    }

    /**
     * @return int count($this->functionScoreCollection)
     */
    public function getFunctionScoreCollectionLength(): int
    {
        return count($this->functionScoreCollection);
    }

    /**
     * @param string $functionScoreBoostMode
     */
    public function setFunctionScoreBoostMode(string $functionScoreBoostMode): void
    {
        $this->functionScoreBoostMode = $functionScoreBoostMode;
    }

    /**
     * @inheritDoc
     */
    public function getQueryBody(): array
    {
        $queryBody = BaseQueryBuilder::getQueryBody();
        if (isset($queryBody['query']['function_score'])) {
            $queryBody['query']['function_score']['boost_mode'] = $this->functionScoreBoostMode;
        }

        return $queryBody;
    }

}