<?php


namespace AppBundle\Services\User;


use Wamcar\User\Hobby;
use Wamcar\User\HobbyRepository;

class HobbyService
{

    /** @var HobbyRepository */
    private $hobbyRepository;

    /**
     * HobbyService constructor.
     * @param HobbyRepository $hobbyRepository
     */
    public function __construct(HobbyRepository $hobbyRepository)
    {
        $this->hobbyRepository = $hobbyRepository;
    }

    /**
     * @param Hobby $hobby
     */
    public function deleteHobby(Hobby $hobby)
    {
        $this->hobbyRepository->remove($hobby);
    }
}