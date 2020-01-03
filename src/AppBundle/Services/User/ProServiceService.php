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
     * @param string $slug
     * @return ProService|object|null
     */
    public function getProServiceByNames(array $proServiceNames)
    {
        return $this->proServiceRepository->findByNames($proServiceNames);
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