<?php

namespace AppBundle\Elasticsearch\Elastica;


use Elastica\Client;

class EntityIndexBuilder
{
    private $client;
    private $indexName;
    private $settings;

    public function __construct(Client $client, string $indexName, array $config)
    {
        $this->client = $client;
        $this->indexName = $indexName;
        $this->settings = $config;
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexName;
    }

    public function create()
    {
        $index = $this->client->getIndex($this->indexName);
        $index->create($this->settings, true);

        return $index;
    }
}