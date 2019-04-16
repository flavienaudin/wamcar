<?php

namespace AppBundle\Services\User;


use Doctrine\DBAL\DBALException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\User\BaseUser;
use Wamcar\User\Enum\LeadStatus;
use Wamcar\User\Lead;
use Wamcar\User\LeadRepository;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;
use Wamcar\User\UserRepository;

class LeadManagementService
{

    /** @var UserRepository */
    private $userRepository;
    /** @var LeadRepository */
    private $leadRepository;
    /** @var RouterInterface */
    private $router;
    /** @var TranslatorInterface */
    private $translator;
    /** @var LoggerInterface */
    private $logger;

    /**
     * LeadManagementService constructor.
     * @param UserRepository $userRepository
     * @param LeadRepository $leadRepository
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     */
    public function __construct(UserRepository $userRepository, LeadRepository $leadRepository, RouterInterface $router, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->leadRepository = $leadRepository;
        $this->router = $router;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * DataTables.net Ajax Request
     * $params and $result definitions : https://datatables.net/manual/server-side
     * @param ProUser $proUser
     * @param array $params
     * @return array
     */
    public function getLeadsForDashboard(ProUser $proUser, array $params): array
    {
        $selectedLeads = $this->leadRepository->getLeadsByRequest($proUser, $params);
        $result = [
            "draw" => intval($params['draw']),
            "recordsTotal" => count($proUser->getLeads()),
            "recordsFiltered" => $selectedLeads['recordsFilteredCount'],
            "data" => []
        ];
        /** @var Lead $lead */
        foreach ($selectedLeads['data'] as $lead) {
            $leadName = null;
            if ($lead->getUserLead() instanceof ProUser) {
                $leadName = '<a href="' . $this->router->generate('front_view_pro_user_info', [
                        'slug' => $lead->getUserLead()->getSlug()
                    ], UrlGeneratorInterface::ABSOLUTE_URL) . '">' . $lead->getFullName() . '</a>';
            } elseif ($lead->getUserLead() instanceof PersonalUser) {
                $leadName = '<a href="' . $this->router->generate('front_view_personal_user_info', [
                        'slug' => $lead->getUserLead()->getSlug()
                    ], UrlGeneratorInterface::ABSOLUTE_URL) . '">' . $lead->getFullName() . '</a>';
            } else {
                $leadName = $lead->getFullName();
            }

            $status = '<select class="js-change-status">';
            foreach (LeadStatus::values() as $leadStatusKey => $leadStatusValue) {
                $selected = '';
                if ($lead->getStatus()->getKey() === $leadStatusKey) {
                    $selected = 'selected="selected"';
                }
                $status .= '<option value="' . $this->router->generate('front_change_lead_status', [
                        'id' => $lead->getId(), 'leadStatus' => $leadStatusKey
                    ], UrlGeneratorInterface::ABSOLUTE_URL) . '" ' . $selected . '>' .
                    $this->translator->trans($leadStatusValue, [], 'enumeration') . '</option>';
            }
            $nbSales = count($lead->getSaleDeclarations());
            $action = '<ul class="no-bullet no-margin">';
            $action .= '<li>' . $this->translator->transChoice('lead.sales', $nbSales, ['%nbSales%' => $nbSales]) . '</li>';
            $action .= '<li><a href="' . $this->router->generate('front_sale_declaration_new', [
                    'leadId' => $lead->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL) . '">' . $this->translator->trans('lead.add_sale') . '</a></li>';
            $action .= '</ul>';
            $status .= '</select>';

            $result['data'][] = [
                'leadName' => $leadName,
                'lastContactAt' => $lead->getLastContactedAt()->format("d/m/y H:i"),
                'proPhoneStats' => $lead->getNbPhoneProAction(),
                'profilePhoneStats' => $lead->getNbPhoneAction(),
                'messageStats' => $lead->getNbMessages(),
                'likeStats' => $lead->getNbLikes(),
                'status' => $status,
                'action' => $action
            ];
        }
        return $result;
    }

    /**
     * Generate and intialise the leads of the $proUser, based on the conversation and the likes
     * @param ProUser $proUser
     * @return int the number of $proUser's leads
     */
    public function generateProUserLead(ProUser $proUser)
    {
        $potentialLeads = $this->retrievePotentialLeads($proUser);
        foreach ($potentialLeads as $leadInfo) {
            /** @var BaseUser $leadUser */
            $leadUser = $this->userRepository->findIgnoreSoftDeleted($leadInfo['leadUserId']);
            if ($leadUser != null) {
                $lead = $this->getLead($proUser, $leadUser, false);
                if ($lead != null) {
                    $lead->setNbMessages($leadInfo['nbMessages']);
                    $lead->setNbLikes($leadInfo['nbLikes']);
                    try {
                        $lead->setCreatedAt(new \DateTime($leadInfo['createdAt']));
                        $lead->setLastContactedAt(new \DateTime($leadInfo['contactedAt']));
                    } catch (\Exception $e) {
                        $this->logger->critical('Error while setting Lead Dates (now are used by default) ' . $lead->getId());
                        $this->logger->critical(print_r($leadInfo, true));
                        // Note : update with 'now' date in lastContactedAt and createdAt even if error
                    }
                    $this->leadRepository->update($lead);
                }
            }
        }
        return count($proUser->getLeads());
    }

    /**
     * Retrieve BaseUsers in conversation with the $proUser and who likes $proUser's vehicle
     * @param ProUser $proUser
     * @return array
     */
    private function retrievePotentialLeads(ProUser $proUser)
    {
        try {
            return $this->leadRepository->getPotentialLeadsByProUser($proUser);
        } catch (DBALException $e) {
            return [];
        }
    }

    /**
     * Get or create a $proUser's Lead of $userLead
     * @param ProUser $proUser
     * @param BaseUser $user
     * @param bool $bddSave if true the new Lead is persisted in the db
     * @return Lead|null null if trying to get himself lead
     */
    public function getLead(ProUser $proUser, BaseUser $user, bool $bddSave = true): ?Lead
    {
        if ($proUser->is($user)) {
            return null;
        }
        $lead = $this->leadRepository->findOneBy(['proUser' => $proUser, 'userLead' => $user]);
        if ($lead == null) {
            $lead = $this->createLead($proUser, $user, $bddSave);
        }
        return $lead;
    }

    /**
     * Create a new $proUser's Lead of $userLead
     * @param ProUser $proUser
     * @param BaseUser $userLead
     * @param bool $bddSave if true the new Lead is persisted in the db
     * @return null|Lead null if trying to create himself lead
     */
    public function createLead(ProUser $proUser, BaseUser $userLead, bool $bddSave = true): ?Lead
    {
        if ($proUser->is($userLead)) {
            return null;
        }
        $lead = new Lead($proUser, $userLead);
        if ($bddSave) {
            $this->leadRepository->add($lead);
        }
        return $lead;
    }

    /**
     * Create/Update the $messageSender's lead of the $proUser by incrementing the number of messages
     * @param ProUser $proUser
     * @param BaseUser $messageSender
     * @return Lead|null if message to himself
     */
    public function increaseMessageNumberOfProUser(ProUser $proUser, BaseUser $messageSender): ?Lead
    {
        $lead = $this->getLead($proUser, $messageSender, true);
        if ($lead != null) {
            $lead->increaseNbMessages();
            return $this->leadRepository->update($lead);
        }
        return null;

    }

    /**
     * Create/Update the $liker's lead of the $proUser by incrementing the number of likes
     * @param ProUser $proUser
     * @param BaseUser $liker
     * @param bool $increase if true then nb of likes is increased, otherwise it is decreased
     * @return Lead|null if like to its own vehicle
     */
    public function updateLikeNumberOfProUser(ProUser $proUser, BaseUser $liker, bool $increase = true): ?Lead
    {
        $lead = $this->getLead($proUser, $liker, false);
        if ($lead != null) {
            $lead->increaseNbLikes($increase ? 1 : -1);
            return $this->leadRepository->update($lead);
        }
        return null;
    }

    /**
     * Create/Update the $messageSender's lead of the $proUser by incrementing the number of messages
     * @param ProUser $proUser
     * @param BaseUser $messageSender
     * @param bool $phonePro If true the phone number concerned is the ProUser.phonePro
     * @return Lead|null if $proUser's own phoneNumber
     */
    public function increasePhoneNumberOfProUser(ProUser $proUser, BaseUser $messageSender, bool $phonePro): ?Lead
    {
        $lead = $this->getLead($proUser, $messageSender, false);
        if ($lead != null) {
            if ($phonePro) {
                $lead->increaseNbPhoneProAction();
            } else {
                $lead->increaseNbPhoneAction();
            }
            return $this->leadRepository->update($lead);
        }
        return null;
    }

    /**
     * @param Lead $lead
     * @param LeadStatus $leadStatus
     * @return Lead
     */
    public function changeLeadStatus(Lead $lead, LeadStatus $leadStatus)
    {
        $lead->setStatus($leadStatus);
        return $this->leadRepository->update($lead);
    }

}