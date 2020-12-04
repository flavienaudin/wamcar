<?php

namespace AppBundle\Elasticsearch\Elastica;


use Elastica\Client;
use Elastica\Document;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\Type;
use Novaway\ElasticsearchClient\Indexable;

class EntityIndexer
{
    /** @var Client */
    protected $client;

    /** @var string */
    protected $indexName;

    /**
     * VehicleInfoIndexer constructor.
     * @param Client $client
     * @param string $indexName
     */
    public function __construct(Client $client, string $indexName)
    {
        $this->client = $client;
        $this->indexName = $indexName;
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexName;
    }

    /**
     * @param Indexable $data
     * @param Type|string $type OPTIONAL Type name
     * @return Document
     */
    public function buildDocument(Indexable $data, $type = '_doc'): Document
    {
        return new Document($data->getId(), $data->toArray(), $type);
    }

    /**
     * Index (Add/Update) or Delete the given Indexable
     * @param Indexable $indexable
     * @return array : [indexed => boolean, response => Response
     */
    public function updateIndexable(Indexable $indexable)
    {
        if ($indexable->shouldBeIndexed()) {
            return [
                'indexed'=> true,
                'response' => $this->indexAllDocuments([$indexable], false)
            ];
        } else {
            return [
                'indexed'=> false,
                'response' => $this->deleteByIds([$indexable->getId()])
            ];
        }
    }

    /**
     * @param array $datas Array of Documents or Indexable entities
     * @param bool $areDocuments true indicates $data is already an array of Document
     * @param int $batchSize Size of bulk of documents to add to ES
     * @return Response
     */
    public function indexAllDocuments(array $datas, bool $areDocuments = false, ?int $batchSize = 3000): Response
    {
        $index = $this->client->getIndex($this->indexName);
        if ($areDocuments) {
            $documents = $datas;
        } else {
            $documents = [];
            /** @var Indexable $data */
            foreach ($datas as $data) {
                if ($data->shouldBeIndexed()) {
                    $document = $this->buildDocument($data);
                    $documents[] = $document;
                }
            }
        }
        $docs = array_chunk($documents, $batchSize);
        foreach($docs as $d) {
            $index->addDocuments($d);
        }
        return $index->refresh();
    }

    /**
     * Delete all documents of the entity index or the given list of documents.
     *
     * @param null|array $datas
     * @param bool $areDocuments true indicates $data is already an array of Document
     */
    public function deleteAllDocuments(?array $datas = null, bool $areDocuments = false)
    {
        $index = $this->client->getIndex($this->indexName);
        if ($datas == null) {
            $index->deleteByQuery(new MatchAll());
        } else {
            if ($areDocuments) {
                $documents = $datas;
            } else {
                $documents = [];
                /** @var Indexable $data */
                foreach ($datas as $data) {
                    if (!$data->shouldBeIndexed()) {
                        $documents[] = $this->buildDocument($data);
                    }
                }
            }
            $index->deleteDocuments($documents);
        }
        $index->refresh();
    }

    /**
     * Delete documents by their Ids and optionnal "$type", on this index or on the given index
     * @param array $ids Document ids
     * @param string|null $indexName
     * @param string|\Elastica\Type $type Type of documents
     * @param string|bool $routing Optional routing key for all ids
     * @return Response
     */
    public function deleteByIds(array $ids, ?string $indexName = null, $type = '_doc', $routing = false): Response
    {
        $index = $this->client->getIndex($indexName ?? $this->indexName);
        $this->client->deleteIds($ids, $index, $type, $routing);
        return $index->refresh();
    }

    /**
     * Search the query on this index or the given index
     * @param Query $query
     * @param string|null $indexName
     * @return ResultSet
     */
    public function search(Query $query, ?string $indexName = null): ResultSet
    {
        $index = $this->client->getIndex($indexName ?? $this->indexName);
        return $index->search($query);
    }

    /**
     * Search all documents
     * @return ResultSet
     */
    public function searchAllDocuments(): ResultSet
    {
        $index = $this->client->getIndex($this->indexName);
        return $index->search(new MatchAll());
    }
}