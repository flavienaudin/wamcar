<?php


namespace AppBundle\Services\User;


use Wamcar\User\ProService;
use Wamcar\User\ProServiceCategory;
use Wamcar\User\ProServiceCategoryRepository;
use Wamcar\User\ProServiceRepository;

class ProServiceService
{
    /** @var ProServiceRepository */
    private $proServiceRepository;
    /** @var ProServiceCategoryRepository */
    private $proServiceCategoryRepository;

    /**
     * ProServiceService constructor.
     * @param ProServiceRepository $proServiceRepository
     * @param ProServiceCategoryRepository $proServiceCategoryRepository
     */
    public function __construct(ProServiceRepository $proServiceRepository, ProServiceCategoryRepository $proServiceCategoryRepository)
    {
        $this->proServiceRepository = $proServiceRepository;
        $this->proServiceCategoryRepository = $proServiceCategoryRepository;
    }

    /**
     * @param string $slug
     * @return ProService|object|null
     */
    public function getProServiceBySlug(string $slug)
    {
        return $this->proServiceRepository->findOneBy([
            'slug' => $slug
        ]);
    }

    /**
     * @param array $proServiceNames
     * @param bool $orderByName
     * @return mixed
     */
    public function getProServiceByNames(array $proServiceNames, bool $orderByName = true)
    {
        return $this->proServiceRepository->findByNames($proServiceNames,$orderByName);
    }

    public function deleteProService(ProService $proService)
    {
        $this->proServiceRepository->remove($proService);
    }

    public function deleteProServiceCategory(ProServiceCategory $proServiceCategory)
    {
        $this->proServiceCategoryRepository->remove($proServiceCategory);
    }
}