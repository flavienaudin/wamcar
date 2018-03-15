<?php

namespace AppBundle\Security;


use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\UserPicture;
use AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository;
use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Doctrine\Repository\DoctrineUserRepository;
use AppBundle\Form\DTO\RegistrationDTO;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;

class UserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    const REGISTRATION_TYPE_SESSION_KEY = 'registration_type';

    /** @var DoctrineUserRepository */
    private $doctrineUserRepository;
    /** @var DoctrineProUserRepository */
    private $doctrineProUserRepository;
    /** @var DoctrinePersonalUserRepository */
    private $doctrinePersonalUserRepository;
    /** @var UserRegistrationService */
    private $userRegistrationService;
    /** @var SessionInterface */
    private $session;

    public function __construct(DoctrineUserRepository $doctrineUserRepository,
                                DoctrineProUserRepository $doctrineProUserRepository,
                                DoctrinePersonalUserRepository $doctrinePersonalUserRepository,
                                UserRegistrationService $userRegistrationService,
                                SessionInterface $session)
    {
        $this->doctrineUserRepository = $doctrineUserRepository;
        $this->doctrineProUserRepository = $doctrineProUserRepository;
        $this->doctrinePersonalUserRepository = $doctrinePersonalUserRepository;
        $this->userRegistrationService = $userRegistrationService;
        $this->session = $session;
    }

    /**
     * Loads the user by a given UserResponseInterface object.
     *
     * @param UserResponseInterface $response
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $userServiceId = $response->getUsername();
        $service = $response->getResourceOwner()->getName();

        $user = $this->doctrineUserRepository->findOneBy([$service . 'Id' => $userServiceId]);

        //when the user is registrating
        if (null === $user) {
            $user = $this->doctrineUserRepository->findOneByEmail($response->getEmail());

            $setter = 'set' . ucfirst($service);
            $setter_id = $setter . 'Id';
            $setter_token = $setter . 'AccessToken';

            if ($user == null) {
                // get the registration type and check its validity
                if (!$this->session->has(self::REGISTRATION_TYPE_SESSION_KEY)) {
                    throw new UsernameNotFoundException("flash.error.no_registration_type");
                }

                $registrationType = $this->session->get(self::REGISTRATION_TYPE_SESSION_KEY);
                if ($registrationType != ProUser::TYPE && $registrationType != PersonalUser::TYPE) {
                    $registrationType = PersonalUser::TYPE;
                }
                $this->session->remove(self::REGISTRATION_TYPE_SESSION_KEY);

                $registrationDTO = new RegistrationDTO($registrationType);
                $registrationDTO->socialNetworkOrigin = $service;

                $registrationDTO->email = $response->getEmail();
                $registrationDTO->firstName = $response->getFirstName();
                $registrationDTO->lastName = $response->getLastName();

                $user = $this->userRegistrationService->registerUser($registrationDTO, false);
            }

            $user->$setter_id($userServiceId);
            $user->$setter_token($response->getAccessToken());

            // TODO récupérer les données disponibles

            $urlPicture = $response->getProfilePicture();
            if ($user->getAvatar() === null && !empty($urlPicture)) {
                $parsedUrl = parse_url($urlPicture);
                $originalFileName = last(explode("/", $parsedUrl['path']));
                $tmpDirPictureFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $originalFileName;
                if (file_put_contents($tmpDirPictureFilename, fopen($urlPicture, "r")) !== false) {
                    try {
                        $uploadedFile = new UploadedFile($tmpDirPictureFilename, $originalFileName, mime_content_type($tmpDirPictureFilename), filesize($tmpDirPictureFilename), null, true);
                        $picture = new UserPicture($user, $uploadedFile);
                        $user->setAvatar($picture);
                    } catch (FileNotFoundException $fileNotFoundException) {

                    }
                }
            }
            $this->doctrineUserRepository->update($user);
        }
        return $user;
    }


    /**
     * @param string $username
     * @return null|ApplicationUser
     */
    public function loadUserByUsername($username): ?ApplicationUser
    {
        return $this->doctrineUserRepository->findOneByEmail($username);
    }

    /**
     * @param UserInterface $user
     * @return null|ApplicationUser
     */
    public function refreshUser(UserInterface $user): ?ApplicationUser
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return ApplicationUser::class === $class;
    }
}