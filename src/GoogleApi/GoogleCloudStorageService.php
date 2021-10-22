<?php


namespace GoogleApi;


use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;
use Wamcar\VideoCoaching\VideoProjectDocument;

class GoogleCloudStorageService
{

    /** @var StorageClient */
    private $storageClient;
    /** @var string */
    private $bucketnamePrefix;
    /** @var NamerInterface */
    private $namer;
    /** @var LoggerInterface */
    private $logger;

    /**
     * GoogleCloudStorageService constructor.
     * @param string $keyFilePath
     * @param string $bucketnamePrefix
     * @param NamerInterface $namer
     * @param LoggerInterface $logger
     */
    public function __construct(string $keyFilePath, string $bucketnamePrefix, NamerInterface $namer, LoggerInterface $logger)
    {
        $this->storageClient = new StorageClient([
            'keyFilePath' => $keyFilePath
        ]);
        $this->bucketnamePrefix = $bucketnamePrefix;
        $this->namer = $namer;
        $this->logger = $logger;
    }

    /**
     * @return string|null
     */
    public function createVideoProjectBucketName(): ?string
    {
        $bucketName = null;
        /** @var Bucket|null $bucket */
        $bucket = null;
        $triesCounter = 0;
        do {
            try {

                $bucketName = $this->bucketnamePrefix . "-" . Uuid::uuid4();
                $bucket = $this->storageClient->bucket($bucketName);
                $triesCounter++;
            } catch (\Exception $uuidException) {
                $this->logger->debug($uuidException->getMessage());
                $bucket = null;
            }
        } while (($bucket == null || $bucket->exists()) && $triesCounter < 10);
        if ($bucket == null) {
            $this->logger->error("Bucket creation : too much tries");
            return null;
        }

        try {
            $storageClass = 'STANDARD';
            $location = 'EU';
            $bucket = $this->storageClient->createBucket($bucketName, [
                'storageClass' => $storageClass,
                'location' => $location,
                'iamConfiguration' => [
                    'uniformBucketLevelAccess' => ['enabled' => true]
                ]
            ]);
        } catch (GoogleException $googleException) {
            $this->logger->error('[BUCKET CREATION] ' . $googleException->getMessage());
            return null;
        }

        return $bucket->name() ?? null;
    }

    /**
     * @param VideoProjectDocument $videoProjectDocument
     * @return string
     */
    public function storeFile(VideoProjectDocument $videoProjectDocument)
    {
        /** @var Bucket $bucket */
        $bucket = $this->storageClient->bucket($videoProjectDocument->getVideoProject()->getGoogleStorageBucketName());

        $mappings = new PropertyMapping('file', "filename_property", [
            'mapping' => 'videoproject_document',
            'filename_property' => 'fileName',
            'size' => 'fileSize',
            'mime_type' => 'fileMimeType',
            'original_name' => 'fileOriginalName'
        ]);
        $newName = 'library/' . $this->namer->name($videoProjectDocument, $mappings);
        $bucket->upload(fopen($videoProjectDocument->getFile()->getRealPath(), 'r'), [
            'name' => $newName
        ]);
        return $newName;
    }

    /**
     * @param VideoProjectDocument $videoProjectDocument
     * @return bool
     */
    public function existsFile(VideoProjectDocument $videoProjectDocument): bool
    {
        /** @var Bucket $bucket */
        $bucket = $this->storageClient->bucket($videoProjectDocument->getVideoProject()->getGoogleStorageBucketName());
        $document = $bucket->object($videoProjectDocument->getFileName());
        return $document->exists();
    }

    /**
     * @param VideoProjectDocument $videoProjectDocument
     */
    public function deleteFile(VideoProjectDocument $videoProjectDocument)
    {
        /** @var Bucket $bucket */
        $bucket = $this->storageClient->bucket($videoProjectDocument->getVideoProject()->getGoogleStorageBucketName());
        $document = $bucket->object($videoProjectDocument->getFileName());
        $document->delete();
    }

    /**
     * @param VideoProjectDocument $videoProjectDocument
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getFileStreamOfDocument(VideoProjectDocument $videoProjectDocument)
    {
        /** @var Bucket $bucket */
        $bucket = $this->storageClient->bucket($videoProjectDocument->getVideoProject()->getGoogleStorageBucketName());
        $document = $bucket->object($videoProjectDocument->getFileName());
        return $document->downloadAsStream();
    }
}
