<?php

namespace AppBundle\Security;


use AppBundle\Controller\Front\SecurityController;
use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\Doctrine\Entity\UserPicture;
use AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository;
use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Doctrine\Repository\DoctrineUserRepository;
use AppBundle\Form\DTO\RegistrationDTO;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;
use Wamcar\User\Title;

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
    /** @var LoggerInterface */
    private $logger;
    /** @var UrlGeneratorInterface */
    protected $router;

    public function __construct(DoctrineUserRepository $doctrineUserRepository,
                                DoctrineProUserRepository $doctrineProUserRepository,
                                DoctrinePersonalUserRepository $doctrinePersonalUserRepository,
                                UserRegistrationService $userRegistrationService,
                                SessionInterface $session, LoggerInterface $logger, UrlGeneratorInterface $router)
    {
        $this->doctrineUserRepository = $doctrineUserRepository;
        $this->doctrineProUserRepository = $doctrineProUserRepository;
        $this->doctrinePersonalUserRepository = $doctrinePersonalUserRepository;
        $this->userRegistrationService = $userRegistrationService;
        $this->session = $session;
        $this->logger = $logger;
        $this->router = $router;
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

                $registrationDTO = new RegistrationDTO($registrationType);
                $registrationDTO->socialNetworkOrigin = $service;
                $registrationDTO->password = uniqid("pwd");

                $registrationDTO->email = $response->getEmail();
                if (empty($registrationDTO->email)) {
                    $this->session->remove(self::REGISTRATION_TYPE_SESSION_KEY);
                    throw new UnsupportedUserException("flash.error.social_account_without_email");
                }
                $registrationDTO->firstName = $response->getFirstName();
                $registrationDTO->lastName = $response->getLastName();

                $user = $this->userRegistrationService->registerUser($registrationDTO, false);
            }

            $user->$setter_id($userServiceId);
            $user->$setter_token($response->getAccessToken());

            if (empty($user->getLastName())) {
                $user->getUserProfile()->setLastName($response->getLastName());
            }
            $responseData = $response->getData();
            if (empty($user->getDescription()) && array_key_exists('about', $responseData)) {
                $user->getUserProfile()->setDescription($responseData['about']);
            }
            if (empty($user->getTitle()) && array_key_exists('gender', $responseData)) {
                $user->getUserProfile()->setTitle(Title::convertGender($responseData['gender']));
            }


            $urlPicture = $response->getProfilePicture();
            if ($user->getAvatar() === null && !empty($urlPicture)) {
                $parsedUrl = parse_url($urlPicture);
                $originalFileName = uniqid("avatar") . "_" . last(explode("/", $parsedUrl['path']));
                $tmpDirPictureFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $originalFileName;
                if (file_put_contents($tmpDirPictureFilename, fopen($urlPicture, "r")) !== false) {
                    try {
                        $uploadedFile = new UploadedFile($tmpDirPictureFilename, $originalFileName, mime_content_type($tmpDirPictureFilename), filesize($tmpDirPictureFilename), null, true);
                        $picture = new UserPicture($user, $uploadedFile);
                        $user->setAvatar($picture);
                    } catch (FileNotFoundException $fileNotFoundException) {
                        $this->logger->warning($fileNotFoundException->getMessage());
                    }
                }
            }
            $this->doctrineUserRepository->update($user);

            // Add the query param to track the inscription
            if ($this->session->has('_security.front.target_path')) {
                $url_targeted = $this->session->get('_security.front.target_path');
                if (!str_contains($url_targeted, SecurityController::INSCRIPTION_QUERY_PARAM . '=')) {
                    $queryParam = SecurityController::INSCRIPTION_QUERY_PARAM . "=" . $registrationType . "-" . $service;
                    if (str_contains($url_targeted, '?')) {
                        $this->session->set('_security.front.target_path', $url_targeted . "&" . $queryParam);
                    } else {
                        $this->session->set('_security.front.target_path', $url_targeted . "?" . $queryParam);
                    }
                }
            }
        } else {
            // if target_path is set to REGISTER_ORIENTATION path while the user is already registred Then redirection to its profile
            if (str_start($this->session->get('_security.front.target_path'), $this->router->generate('register_orientation', [], UrlGeneratorInterface::ABSOLUTE_URL))) {
                $this->session->set('_security.front.target_path', $this->router->generate('front_view_current_user_info', [], UrlGeneratorInterface::ABSOLUTE_URL));
            }
        }
        $this->session->remove(self::REGISTRATION_TYPE_SESSION_KEY);
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