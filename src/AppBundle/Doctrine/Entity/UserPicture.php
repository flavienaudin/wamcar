<?php

namespace AppBundle\Doctrine\Entity;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;
use Wamcar\User\BaseUser;
use Wamcar\User\Picture;

class UserPicture extends Picture implements ApplicationPicture
{
    use FileHolderTrait;

    /** @var string */
    private $id;

    /**
     * UserPicture constructor.
     * @param BaseUser $user
     * @param File $file
     */
    public function __construct(BaseUser $user, File $file)
    {
        $this->id = Uuid::uuid4();
        $this->setFile($file);
        parent::__construct($user);
    }
}
