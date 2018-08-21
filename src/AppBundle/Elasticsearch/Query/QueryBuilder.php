<?php

namespace AppBundle\Elasticsearch\Query;

use Novaway\ElasticsearchClient\Query\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{

    // ScoreMode : Multiply the _score with the function result (default)
    const MULTIPLY = "multiply";
    // ScoreMode : Add the function result to the _score
    const SUM = "sum";
    // ScoreMode : The lower of the _score and the function result
    const MIN = "min";
    // ScoreMode : The higher of the _score and the function result
    const MAX = "max";
    // ScoreMode : The first function that has a matching filter is applied
    const FIRST = "first";
    // ScoreMode : Scores are averaged
    const AVG = "avg";

    /** @var string $functionScoreMode */
    protected $functionScoreMode;

    public function __construct(int $offset = BaseQueryBuilder::DEFAULT_OFFSET, int $limit = BaseQueryBuilder::DEFAULT_LIMIT, float $minScore = BaseQueryBuilder::DEFAULT_MIN_SCORE, string $boostMode = self::MULTIPLY, string $scoreMode = self::MULTIPLY)
    {
        BaseQueryBuilder::__construct($offset, $limit, $minScore, $boostMode);
        $this->functionScoreMode = $scoreMode;
    }

    /**
     * @return int count($this->functionScoreCollection)
     */
    public function getFunctionScoreCollectionLength(): int
    {
        return count($this->functionScoreCollection);
    }

    /**
     * @param string $functionScoreMode
     * @return QueryBuilder
     */
    public function setFunctionScoreMode(string $functionScoreMode): QueryBuilder
    {
        $this->functionScoreMode = $functionScoreMode;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQueryBody(): array
    {
        $queryBody = BaseQueryBuilder::getQueryBody();
        if (isset($queryBody['query']['function_score'])) {
            $queryBody['query']['function_score']['score_mode'] = $this->functionScoreMode;
        }

        return $queryBody;
    }

}