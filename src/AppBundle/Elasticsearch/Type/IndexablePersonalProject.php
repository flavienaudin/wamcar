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
     * @param int|null $budget
     * @param bool $isFleet
     * @param array $projectVehicles Each ProjectVehicle should be an array compatible with indexation (See static method : createFromPersonalProject)
     */
    public function __construct(int $id, int $userId, ?string $description, ?int $budget, bool $isFleet, array $projectVehicles)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->description = $description;
        $this->budget = $budget;
        $this->isFleet = $isFleet;
        $this->projectVehicles = $projectVehicles;
    }

    /**
     * Build an IndexablePersonalProject from a Project
     * @param Project $project
     * @return IndexablePersonalProject
     */
    public static function createFromPersonalProject(Project $project): IndexablePersonalProject
    {
        $indexablePersonalProject = new IndexablePersonalProject($project->getId(), $project->getPersonalUser()->getId(), $project->getDescription(), $project->getBudget(), $project->isFleet(), []);
        foreach ($project->getProjectVehicles() as $projectVehicle){
            $indexablePersonalProject->projectVehicles[] = [
                'make' => $projectVehicle->getMake(),
                'model' => $projectVehicle->getModel(),
                'yearMin' => $projectVehicle->getYearMin(),
                'mileageMax' => $projectVehicle->getMileageMax(),
                'key_make' => $projectVehicle->getMake(),
                'key_model' => $projectVehicle->getModel()
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
        return !empty($this->description) || !empty($this->budget) || count($this->projectVehicles) > 0;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'description' => $this->description,
            'budget' => $this->budget,
            'isFleet' => $this->isFleet,
            'projectVehicles' => $this->projectVehicles,
        ];
    }

}
