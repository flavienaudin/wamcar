<?php

namespace AppBundle\Elasticsearch\Type;

use Novaway\ElasticsearchClient\Indexable;
use Wamcar\User\Project;

class IndexablePersonalProject implements Indexable
{
    const TYPE = 'personal_project';

    /** @var  int */
    protected $id;
    /** @var  int */
    protected $userId;
    /** @var  null|string */
    protected $description;
    /** @var array|null */
    protected $location;
    /** @var null|int */
    protected $budget;
    /** @var  bool */
    protected $isFleet;
    /** @var array */
    protected $projectVehicles;

    /**
     * IndexablePersonalProject constructor.
     * @param int $id
     * @param int $userId
     * @param null|string $description
     * @param null|array $location (array with "lat" and "lon" as keys
     * @param int|null $budget
     * @param bool $isFleet
     * @param array $projectVehicles Each ProjectVehicle should be an array compatible with indexation (See static method : createFromPersonalProject)
     * @param \DateTimeInterface $updatedAt
     * @param null|\DateTimeInterface $deletedAt
     */
    public function __construct(int $id, int $userId, ?string $description, ?array $location, ?int $budget, bool $isFleet, array $projectVehicles, \DateTimeInterface $updatedAt, ?\DateTimeInterface $deletedAt)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->description = $description;
        $this->location = $location;
        $this->budget = $budget;
        $this->isFleet = $isFleet;
        $this->projectVehicles = $projectVehicles;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }

    /** @var \DateTimeInterface */
    private $updatedAt;

    /** @var \DateTimeInterface */
    private $deletedAt;

    /**
     * Build an IndexablePersonalProject from a Project
     * @param Project $project
     * @return IndexablePersonalProject
     */
    public static function createFromPersonalProject(Project $project): IndexablePersonalProject
    {
        $location = null;
        if($project->getPersonalUser()->getCity() != null ){
            $location =  [
                'lat' => $project->getPersonalUser()->getCity()->getLatitude(),
                'lon' => $project->getPersonalUser()->getCity()->getLongitude()
            ];
        }
        $indexablePersonalProject = new IndexablePersonalProject($project->getId(), $project->getPersonalUser()->getId(), $project->getDescription(), $location , $project->getBudget(), $project->isFleet(), [], $project->getUpdatedAt(), $project->getDeletedAt());
        foreach ($project->getProjectVehicles() as $projectVehicle) {
            $indexablePersonalProject->projectVehicles[] = [
                'make' => $projectVehicle->getMake(),
                'model' => $projectVehicle->getModel(),
                'makeAndModel' => $projectVehicle->getMake() . ' ' . $projectVehicle->getModel(),
                'yearMin' => $projectVehicle->getYearMin(),
                'mileageMax' => $projectVehicle->getMileageMax()
            ];
        }
        return $indexablePersonalProject;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return strval($this->id);
    }

    /**
     * @return bool
     */
    public function shouldBeIndexed(): bool
    {
        return $this->deletedAt == null && (!empty($this->description) || !empty($this->budget) || count($this->projectVehicles) > 0);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'project' => [
                'description' => $this->description,
                'budget' => $this->budget,
                'isFleet' => $this->isFleet,
                'models' => $this->projectVehicles
            ],
            'mainSortingPrice' => $this->budget,
            'mainSortingDate' => $this->updatedAt->format('Y-m-d\TH:i:s\Z'),
            'mainSortingLocation' => $this->location,
            'deletedAt' => $this->deletedAt != null ? $this->deletedAt->format('Y-m-d\TH:i:s\Z') : null
        ];
    }

}
