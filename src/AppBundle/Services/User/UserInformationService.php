<?php


namespace AppBundle\Services\User;


use AppBundle\Doctrine\Repository\DoctrineLikeProVehicleRepository;
use AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository;
use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Doctrine\Repository\DoctrineUserLikeVehicleRepository;
use AppBundle\Services\Picture\PathUserPicture;
use AppBundle\Services\Vehicle\VehicleRepositoryResolver;
use GoogleApi\GAReportingAPIService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Wamcar\Conversation\MessageRepository;
use Wamcar\Sale\SaleDeclarationRepository;
use Wamcar\User\LeadRepository;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;


class UserInformationService
{
    const DATETIME_FORMAT = "d/m/Y H:i";

    /** @var MessageRepository $messageRepository */
    private $messageRepository;
    /** @var VehicleRepositoryResolver */
    private $vehicleRepositoryResolver;
    /** @var DoctrineUserLikeVehicleRepository */
    private $userLikeVehicleRepository;
    /** @var DoctrineLikeProVehicleRepository */
    private $likeProVehicleRepository;
    /** @var LeadRepository */
    private $leadRepository;
    /** @var SaleDeclarationRepository */
    private $saleDeclarationRepository;
    /** @var GAReportingAPIService */
    private $gaReportingApiService;
    /** @var DoctrinePersonalUserRepository */
    private $personalUserRepository;
    /** @var DoctrineProUserRepository */
    private $proUserRepository;
    /** @var PathUserPicture */
    private $pathUserPicture;
    /** @var RouterInterface */
    private $router;

    /**
     * UserInformationService constructor.
     * @param MessageRepository $messageRepository
     * @param VehicleRepositoryResolver $vehicleRepositoryResolver
     * @param DoctrineUserLikeVehicleRepository $userLikeVehicleRepository
     * @param DoctrineLikeProVehicleRepository $likeRepository
     * @param LeadRepository $leadRepository
     * @param SaleDeclarationRepository $saleDeclarationRepository
     * @param GAReportingAPIService $gaReportingApiService
     * @param DoctrinePersonalUserRepository $personalUserRepository
     * @param DoctrineProUserRepository $proUserRepository
     * @param PathUserPicture $pathUserPicture
     * @param RouterInterface $router
     */
    public function __construct(MessageRepository $messageRepository,
                                VehicleRepositoryResolver $vehicleRepositoryResolver,
                                DoctrineUserLikeVehicleRepository $userLikeVehicleRepository,
                                DoctrineLikeProVehicleRepository $likeRepository,
                                LeadRepository $leadRepository,
                                SaleDeclarationRepository $saleDeclarationRepository,
                                GAReportingAPIService $gaReportingApiService,
                                DoctrinePersonalUserRepository $personalUserRepository,
                                DoctrineProUserRepository $proUserRepository,
                                PathUserPicture $pathUserPicture,
                                RouterInterface $router
    )
    {
        $this->messageRepository = $messageRepository;
        $this->vehicleRepositoryResolver = $vehicleRepositoryResolver;
        $this->userLikeVehicleRepository = $userLikeVehicleRepository;
        $this->likeProVehicleRepository = $likeRepository;
        $this->leadRepository = $leadRepository;
        $this->saleDeclarationRepository = $saleDeclarationRepository;
        $this->gaReportingApiService = $gaReportingApiService;
        $this->personalUserRepository = $personalUserRepository;
        $this->proUserRepository = $proUserRepository;
        $this->pathUserPicture = $pathUserPicture;
        $this->router = $router;
    }


    /**
     * @param ProUser $proUser
     * @return array
     */
    public function getProUserPerformances(ProUser $proUser): array
    {
        $performances = [
            // Audience
            'nbUniqueViewsOfProfilePage' => 0,
            'nbTotalContacts' => 0,
            'nbPhoneNumberDisplays' => 0,
            'nbReceivedMessages' => 0,
            'nbReceivedLikes' => 0,
            // Activity
            'nbSentMessages' => 0,
            'nbSentLikes' => 0,
            'nbPhoneNumberViews' => 0,
            'nbLeads' => 0,
            // Results
            'nbSales' => 0,
            'nbPartExchanges' => 0,
            'nbTotalTransactions' => 0,
            'conversionRate' => 0
        ];
        $gaReport = $this->gaReportingApiService->getProUserKPI($proUser);

        // Audience
        $performances['nbUniqueViewsOfProfilePage'] = isset($gaReport['profilePage'][0]) ? (intval($gaReport['profilePage'][0]['uniquePageViews']) ?? 0) : 0;
        $phoneDisplayTotal = isset($gaReport['contactsEvents']['telephone'][0]) ? ($gaReport['contactsEvents']['telephone'][0]['total'] ?? 0) : 0;
        $performances['nbPhoneNumberDisplays'] = $phoneDisplayTotal;
        $performances['nbReceivedMessages'] = $this->messageRepository->getCountReceivedMessages($proUser, 30);
        $performances['nbTotalContacts'] = $performances['nbPhoneNumberDisplays'] + $performances['nbReceivedMessages'];
        $performances['nbReceivedLikes'] = $this->likeProVehicleRepository->getCountReceivedLikes($proUser, 30);

        // Activity
        $performances['nbSentMessages'] = $this->messageRepository->getCountSentMessages($proUser);
        $performances['nbSentLikes'] = $this->userLikeVehicleRepository->getCountSentLikes($proUser);
        $performances['nbPhoneNumberViews'] = isset($gaReport['contactsEvents']['telViews'][0]) ? ($gaReport['contactsEvents']['telViews'][0]['total'] ?? 0) : 0;
        $performances['nbLeads'] = $this->leadRepository->getCountLeadsByLastDateOfContact($proUser);

        // Results
        $performances['nbSales'] = $this->saleDeclarationRepository->getCountSales($proUser);
        $performances['nbPartExchanges'] = $this->saleDeclarationRepository->getCountPartExchanges($proUser);
        $performances['nbTotalTransactions'] = $performances['nbSales'] + $performances['nbPartExchanges'];
        if ($performances['nbLeads'] > 0) {
            $performances['conversionRate'] = $performances['nbTotalTransactions'] / $performances['nbLeads'];
        }
        return $performances;
    }

    /**
     * Retrieve the statistics about all personal users
     * @param array $params
     * @param null|int $limit
     * @param null|int $offset
     * @return array
     */
    public function getPersonalUsersStatistics(array $params, int $limit = null, int $offset = null): array
    {
        $personalUsers = $this->personalUserRepository->findIgnoreSoftDeletedBy([], ['createdAt' => 'DESC'], $limit, $offset);
        $result = [
            "draw" => intval($params['draw']),
            "recordsTotal" => count($personalUsers),
            "recordsFiltered" => count($personalUsers),
            "data" => []
        ];
        /** @var PersonalUser $personalUser */
        foreach ($personalUsers as $personalUser) {
            if ($personalUser->getDeletedAt() == null) {
                $personalUserId = '<a href="' . $this->router->generate('front_view_personal_user_info', [
                        'slug' => $personalUser->getSlug()
                    ], UrlGeneratorInterface::ABSOLUTE_URL) . '" target="_blank">' . $personalUser->getId() . '</a>';
            } else {
                $personalUserId = '<span class="text-line-through">' . $personalUser->getId() . '</span>';
            }
            $userPicturePath = $this->pathUserPicture->getPath($personalUser->getAvatar(), 'user_mini_thumbnail', $personalUser->getFirstName());
            $personalUserAvatar = '<span class="user-thumbnail-mini">
                            <img src="' . $userPicturePath . '" alt="' . $personalUser->getFullName() . '"></span>';


            $personalUserActionsData = $this->leadRepository->getLeadUserActionsStats($personalUser);

            $result['data'][] = [
                $personalUserId,
                $personalUserAvatar,
                $personalUser->getFirstName(),
                $personalUser->getLastName(),
                $personalUser->getEmail(),
                $personalUser->getPhone(),
                $personalUser->getCreatedAt() != null ? $personalUser->getCreatedAt()->format(self::DATETIME_FORMAT) : '-',
                $personalUser->getLastSubmissionDate() != null ? $personalUser->getLastSubmissionDate()->format(self::DATETIME_FORMAT) : '-',
                $personalUser->getLastLoginAt() != null ? $personalUser->getLastLoginAt()->format(self::DATETIME_FORMAT) : '-',
                $personalUserActionsData['lastActionDate'] != null ? (new \DateTime($personalUserActionsData['lastActionDate']))->format(self::DATETIME_FORMAT) : '-',
                count($personalUser->getInitiatedConversations()),
                $personalUserActionsData['nbPhoneDisplays'],
                count($personalUser->getPositiveLikes())
            ];
        }
        return $result;
    }

    /**
     * Retrieve the statistics about all pro users
     * @param array $params
     * @param null|int $limit
     * @param null|int $offset
     * @return array
     */
    public function getProUsersStatistics(array $params, int $limit = null, int $offset = null): array
    {
        $proUsers = $this->proUserRepository->findIgnoreSoftDeletedBy([], ['createdAt' => 'DESC'], $limit, $offset);
        $result = [
            "draw" => intval($params['draw']),
            "recordsTotal" => count($proUsers),
            "recordsFiltered" => count($proUsers),
            "data" => []
        ];
        /** @var ProUser $proUser */
        foreach ($proUsers as $proUser) {
            if ($proUser->getDeletedAt() == null) {
                $proUserId = '<a href="' . $this->router->generate('front_view_pro_user_info', [
                        'slug' => $proUser->getSlug()
                    ], UrlGeneratorInterface::ABSOLUTE_URL) . '" target="_blank">' . $proUser->getId() . '</a>';
            } else {
                $proUserId = '<span class="text-line-through">' . $proUser->getId() . '</span>';
            }
            $userPicturePath = $this->pathUserPicture->getPath($proUser->getAvatar(), 'user_mini_thumbnail', $proUser->getFirstName());
            $proUserAvatar = '<span class="user-thumbnail-mini">
                            <img src="' . $userPicturePath . '" alt="' . $proUser->getFullName() . '"></span>';

            $proUserActionsData = $this->leadRepository->getProUserActionsStats($proUser);

            $vehicleRepository = $this->vehicleRepositoryResolver->getVehicleRepositoryByUser($proUser);
            $lastUpdatedVehicle = $vehicleRepository->findBy(['seller' => $proUser], ['updatedAt' => 'DESC'], 1);

            $result['data'][] = [
                $proUserId,
                $proUserAvatar,
                $proUser->getFirstName(),
                $proUser->getLastName(),
                $proUser->getEmail(),
                $proUser->getPhone() . (!empty($proUser->getPhone()) && !empty($proUser->getPhonePro()) ? ' | ' : '') . $proUser->getPhonePro(),
                $proUser->getCreatedAt() != null ? $proUser->getCreatedAt()->format(self::DATETIME_FORMAT) : '-',
                count($lastUpdatedVehicle) == 1 ? $lastUpdatedVehicle[0]->getUpdatedAt()->format(self::DATETIME_FORMAT) : '-',
                $proUser->getLastLoginAt() != null ? $proUser->getLastLoginAt()->format(self::DATETIME_FORMAT) : '-',
                $proUserActionsData['lastActionDate'] != null ? (new \DateTime($proUserActionsData['lastActionDate']))->format(self::DATETIME_FORMAT) : '-',
                count($proUser->getInitiatedConversations()),
                $proUserActionsData['nbPhoneDisplays'],
                count($proUser->getPositiveLikes())
            ];
        }
        return $result;


    }
}