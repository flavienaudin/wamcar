<?php


namespace AppBundle\Form\DTO;


use Wamcar\User\ProUser;
use Wamcar\User\ProUserProService;

class ProUserProSpecialitiesDTO
{
    /** @var ProUserProServiceSpecialityDTO[]|array */
    private $proUserProServicesForSpecialities;

    /**
     * ProUserProSpecialitiesDTO constructor.
     * @param ProUser $proUser
     */
    public function __construct(ProUser $proUser)
    {
        $this->proUserProServicesForSpecialities = [];
        /** @var ProUserProService $proUserProService */
        foreach ($proUser->getProUserProServices() as $proUserProService) {
            $this->proUserProServicesForSpecialities[$proUserProService->getId()] = new ProUserProServiceSpecialityDTO(
                $proUserProService->getId(),
                $proUserProService->getProService()->getName(),
                $proUserProService->isSpeciality()
            );
        }
        uasort($this->proUserProServicesForSpecialities, function(ProUserProServiceSpecialityDTO $s1, ProUserProServiceSpecialityDTO$s2){
            return strcmp($s1->getProServiceName(), $s2->getProServiceName());
        });
    }

    /**
     * @return ProUserProServiceSpecialityDTO[]|array
     */
    public function getProUserProServicesForSpecialities()
    {
        return $this->proUserProServicesForSpecialities;
    }

    /**
     * @param ProUserProServiceSpecialityDTO[]|array $proUserProServicesForSpecialities
     */
    public function setProUserProServicesForSpecialities($proUserProServicesForSpecialities): void
    {
        $this->proUserProServicesForSpecialities = $proUserProServicesForSpecialities;
    }
}