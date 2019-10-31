<?php


namespace AppBundle\Services\User;


use Wamcar\User\ProService;
use Wamcar\User\ProServiceRepository;

class ProServiceService
{
    /** @var ProServiceRepository */
    private $proServiceRepository;

    /**
     * ProServiceService constructor.
     * @param ProServiceRepository $proServiceRepository
     */
    public function __construct(ProServiceRepository $proServiceRepository)
    {
        $this->proServiceRepository = $proServiceRepository;
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

    public function deleteProService(ProService $proService)
    {
        $this->proServiceRepository->remove($proService);
    }

}