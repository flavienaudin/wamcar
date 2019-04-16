<?php


namespace AppBundle\Services\User;


use AppBundle\Doctrine\Repository\DoctrineLikeProVehicleRepository;
use AppBundle\Doctrine\Repository\DoctrineUserLikeVehicleRepository;
use GoogleApi\GAReportingAPIService;
use Wamcar\Conversation\MessageRepository;
use Wamcar\Sale\SaleDeclarationRepository;
use Wamcar\User\LeadRepository;
use Wamcar\User\ProUser;


class UserInformationService
{
    /** @var MessageRepository $messageRepository */
    private $messageRepository;
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

    /**
     * UserInformationService constructor.
     * @param MessageRepository $messageRepository
     * @param DoctrineUserLikeVehicleRepository $userLikeVehicleRepository
     * @param DoctrineLikeProVehicleRepository $likeRepository
     * @param LeadRepository $leadRepository
     * @param SaleDeclarationRepository $saleDeclarationRepository
     * @param GAReportingAPIService $gaReportingApiService
     */
    public function __construct(MessageRepository $messageRepository,
                                DoctrineUserLikeVehicleRepository $userLikeVehicleRepository,
                                DoctrineLikeProVehicleRepository $likeRepository,
                                LeadRepository $leadRepository,
                                SaleDeclarationRepository $saleDeclarationRepository,
                                GAReportingAPIService $gaReportingApiService)
    {
        $this->messageRepository = $messageRepository;
        $this->userLikeVehicleRepository = $userLikeVehicleRepository;
        $this->likeProVehicleRepository = $likeRepository;
        $this->leadRepository = $leadRepository;
        $this->saleDeclarationRepository = $saleDeclarationRepository;
        $this->gaReportingApiService = $gaReportingApiService;
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
            'nbLeads' => 0,
            // Results
            'nbSales' => 0,
            'nbPartExchanges' => 0,
            'nbTotalTransactions' => 0,
            'conversionRate' => 0
        ];

        $gaReport = $this->gaReportingApiService->getProUserKPI($proUser);


        // Audience
        $performances['nbUniqueViewsOfProfilePage'] = intval($gaReport['profilePage'][0]['uniquePageViews']) ?? 0;
        $phoneDisplayTotal = $gaReport['contactsEvents']['telephone'][0]['total'] ?? 0;
        $performances['nbPhoneNumberDisplays'] = $phoneDisplayTotal;
        $performances['nbReceivedMessages'] = $this->messageRepository->getCountReceivedMessages($proUser);
        $performances['nbTotalContacts'] = $performances['nbPhoneNumberDisplays'] + $performances['nbReceivedMessages'];
        $performances['nbReceivedLikes'] = $this->likeProVehicleRepository->getCountReceivedLikes($proUser);

        // Activity
        $performances['nbSentMessages'] = $this->messageRepository->getCountSentMessages($proUser);
        $performances['nbSentLikes'] = $this->userLikeVehicleRepository->getCountSentLikes($proUser);
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
}