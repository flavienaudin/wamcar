<?php

namespace AppBundle\Services\User;


use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Conversation\Message;
use Wamcar\Conversation\MessageRepository;
use Wamcar\User\BaseLikeVehicle;
use Wamcar\User\BaseUser;
use Wamcar\User\Enum\LeadInitiatedBy;
use Wamcar\User\Enum\LeadStatus;
use Wamcar\User\Lead;
use Wamcar\User\LeadRepository;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;
use Wamcar\User\UserLikeVehicleRepository;
use Wamcar\User\UserRepository;

class LeadManagementService
{

    /** @var UserRepository */
    private $userRepository;
    /** @var LeadRepository */
    private $leadRepository;
    /** @var MessageRepository */
    private $messageRepository;
    /** @var UserLikeVehicleRepository */
    private $userLikeVehicleRepository;
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
     * @param MessageRepository $messageRepository
     * @param UserLikeVehicleRepository $userLikeVehicleRepository
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     */
    public function __construct(UserRepository $userRepository, LeadRepository $leadRepository, MessageRepository $messageRepository, UserLikeVehicleRepository $userLikeVehicleRepository, RouterInterface $router, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->leadRepository = $leadRepository;
        $this->messageRepository = $messageRepository;
        $this->userLikeVehicleRepository = $userLikeVehicleRepository;
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
                    ], UrlGeneratorInterface::ABSOLUTE_URL) . '" target="_blank">' . $lead->getFullName() . '</a>';
            } elseif ($lead->getUserLead() instanceof PersonalUser) {
                $leadName = '<a href="' . $this->router->generate('front_view_personal_user_info', [
                        'slug' => $lead->getUserLead()->getSlug()
                    ], UrlGeneratorInterface::ABSOLUTE_URL) . '" target="_blank">' . $lead->getFullName() . '</a>';
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
            $action .= '<li>' . $this->translator->transChoice('lead.table.sales', $nbSales, ['%nbSales%' => $nbSales]) . '</li>';
            $action .= '<li><a href="' . $this->router->generate('front_sale_declaration_new', [
                    'leadId' => $lead->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL) . '">' . $this->translator->trans('lead.add_sale') . '</a></li>';
            $action .= '</ul>';
            $status .= '</select>';

            $result['data'][] = [
                'control' => '<td><span class="icon-plus-circle no-margin"></span></td>',
                'leadName' => $leadName,
                'lastContactAt' => $lead->getLastContactedAt()->format("d/m/y H:i"),
                'proPhoneByProStats' => $lead->getUserLead() instanceof ProUser ? $lead->getNbPhoneProActionByPro() : '-',
                'profilePhoneByProStats' => $lead->getNbPhoneActionByPro(),
                'proMessageStats' => $lead->getNbProMessages(),
                'proLikeStats' => $lead->getNbProLikes(),
                'proPhoneByLeadStats' => $lead->getNbPhoneProActionByLead(),
                'profilePhoneByLeadStats' => $lead->getNbPhoneActionByLead(),
                'leadMessageStats' => $lead->getNbLeadMessages(),
                'leadLikeStats' => $lead->getNbLeadLikes(),
                'status' => $status,
                'action' => $action
            ];
        }
        return $result;
    }

    public function getLeadsAsUserLinkings(array $params): array
    {
        $ordering = [];
        foreach ($params['order'] as $order) {
            switch ($order['column']) {
                case 2:
                    $ordering['createdAt'] = $order['dir'];
                    break;
                case 3:
                    $ordering['lastContactedAt'] = $order['dir'];
                    break;
                case 13:
                    $ordering['status'] = $order['dir'];
                    break;
            }
        }
        $selectedLeads = $this->leadRepository->findBy([], $ordering);
        $result = [
            "draw" => intval($params['draw']),
            "recordsTotal" => count($selectedLeads),
            "recordsFiltered" => count($selectedLeads),
            "data" => []
        ];
        /** @var Lead $lead */
        foreach ($selectedLeads as $lead) {

            $proUserInfos =
                '<a href="' . $this->router->generate('front_view_pro_user_info', [
                    'slug' => $lead->getProUser()->getSlug()],
                    UrlGeneratorInterface::ABSOLUTE_URL) . '" target="blank">' .
                $lead->getProUser()->getFullName() . ' (' . $lead->getProUser()->getId() . ')</a>';

            $leadInfos = $lead->getUserLead() != null ?
                '<a href="' . $this->router->generate($lead->getUserLead()->isPro() ? 'front_view_pro_user_info' : 'front_view_personal_user_info', [
                    'slug' => $lead->getUserLead()->getSlug()],
                    UrlGeneratorInterface::ABSOLUTE_URL) . '" target="blank">' .
                $lead->getUserLead()->getFullName() . '(' . $lead->getUserLead()->getId() . ')</a>' :
                $lead->getFullName() . ' (del)';
            if (LeadInitiatedBy::PRO_USER()->equals($lead->getInitiatedBy())) {
                $userAinfos = $proUserInfos;
                $userBinfos = $leadInfos;
                $userAproPhone = $lead->getNbPhoneProActionByPro();
                $userAprofilePhone = $lead->getNbPhoneActionByPro();
                $userAMessages = $lead->getNbProMessages();
                $userALikes = $lead->getNbProLikes();
                $userBproPhone = $lead->getNbPhoneProActionByLead();
                $userBprofilePhone = $lead->getNbPhoneActionByLead();
                $userBMessages = $lead->getNbLeadMessages();
                $userBLikes = $lead->getNbLeadLikes();
            } else {
                $userAinfos = $leadInfos;
                $userBinfos = $proUserInfos;
                $userBproPhone = $lead->getNbPhoneProActionByPro();
                $userBprofilePhone = $lead->getNbPhoneActionByPro();
                $userBMessages = $lead->getNbProMessages();
                $userBLikes = $lead->getNbProLikes();
                $userAproPhone = $lead->getNbPhoneProActionByLead();
                $userAprofilePhone = $lead->getNbPhoneActionByLead();
                $userAMessages = $lead->getNbLeadMessages();
                $userALikes = $lead->getNbLeadLikes();
            }

            $affinityDegrees = $lead->getProUser()->getAffinityDegreesWith($lead->getUserLead());

            $nbSales = count($lead->getSaleDeclarations());

            $result['data'][] = [
                'userA' => $userAinfos,
                'userB' => $userBinfos,
                'firstContactedAt' => $lead->getCreatedAt()->format("d/m/y H:i"),
                'lastContactedAt' => $lead->getLastContactedAt()->format("d/m/y H:i"),

                'userAproPhone' => $userAproPhone,
                'userAprofilePhone' => $userAprofilePhone,
                'userAMessages' => $userAMessages,
                'userALikes' => $userALikes,

                'userBproPhone' => $userBproPhone,
                'userBprofilePhone' => $userBprofilePhone,
                'userBMessages' => $userBMessages,
                'userBLikes' => $userBLikes,

                'affinityDegree' => $affinityDegrees != null ? $affinityDegrees->getAffinityValue() : '-',
                'leadStatus' => $this->translator->trans($lead->getStatus(), [], 'enumeration'),
                'sales' => $nbSales
            ];
        }
        return $result;
    }

    /**
     * Initialize leads of all ProUsers, based on the conversation and the likes
     * @param null|SymfonyStyle $io
     * @return array [ProUser.Id => ProUser.NbLeads]
     */
    public function generateProUserLeads(?SymfonyStyle $io): array
    {
        // Reset all counters about Messages and Likes
        $res = $this->leadRepository->resetCountersMessageAndLikes();
        if ($io) {
            $io->text('Reset counters (Message/Like) of Leads : ' . $res . ' updated rows');
        }

        // Array of leads with ProUser.id & LeadUser.id as keys
        $leads = [];
        $messages = $this->messageRepository->findBy([], ['publishedAt' => 'ASC']);
        if ($io) {
            $io->text("Messages...");
            $io->progressStart(count($messages));
        }
        array_walk($messages, function (Message $message) use (&$leads, $io) {
            if ($io) {
                $io->progressAdvance();
            }
            /** @var BaseUser $sender */
            $sender = $message->getUser();
            $recipients = $message->getRecipients();
            if ($sender instanceof ProUser) {
                /** @var BaseUser $recipient */
                foreach ($recipients as $recipient) {
                    if (isset($leads[$sender->getId()]) && isset($leads[$sender->getId()][$recipient->getId()])) {
                        $lead = $leads[$sender->getId()][$recipient->getId()];
                    } else {
                        $lead = $this->getLead($sender, $sender, $recipient, false);
                        if ($lead != null) {
                            if (!isset($leads[$sender->getId()])) {
                                $leads[$sender->getId()] = [];
                            }
                            $leads[$sender->getId()][$recipient->getId()] = $lead;
                        }
                    }
                    if ($lead != null) {
                        $lead->increaseNbProMessages();
                        // update createdAt when generating lead from old data
                        if ($lead->getCreatedAt() == null || $lead->getCreatedAt() > $message->getPublishedAt()) {
                            $lead->setCreatedAt($message->getPublishedAt());
                        }
                        if ($lead->getLastContactedAt() == null || $lead->getLastContactedAt() < $message->getPublishedAt() ||
                            ($lead->getNbPhoneProActionByPro() + $lead->getNbPhoneProActionByLead() + $lead->getNbPhoneActionByPro() + $lead->getNbPhoneActionByLead() == 0)
                        ) {
                            $lead->setLastContactedAt($message->getPublishedAt());
                        }
                    }
                }
            }
            if ($sender != null && $sender->getDeletedAt() == null) {
                foreach ($recipients as $recipient) {
                    if ($recipient instanceof ProUser) {
                        if (isset($leads[$recipient->getId()]) && isset($leads[$recipient->getId()][$sender->getId()])) {
                            $lead = $leads[$recipient->getId()][$sender->getId()];
                        } else {
                            $lead = $this->getLead($recipient, $sender, $sender, false);
                            if ($lead != null) {
                                if (!isset($leads[$recipient->getId()])) {
                                    $leads[$recipient->getId()] = [];
                                }
                                $leads[$recipient->getId()][$sender->getId()] = $lead;
                            }
                        }
                        if ($lead != null) {
                            $lead->increaseNbLeadMessages();
                            // update createdAt when generating lead from old data
                            if ($lead->getCreatedAt() == null || $lead->getCreatedAt() > $message->getPublishedAt()) {
                                $lead->setCreatedAt($message->getPublishedAt());
                            }
                            if ($lead->getLastContactedAt() == null || $lead->getLastContactedAt() < $message->getPublishedAt() ||
                                ($lead->getNbPhoneProActionByPro() + $lead->getNbPhoneProActionByLead() + $lead->getNbPhoneActionByPro() + $lead->getNbPhoneActionByLead() == 0)
                            ) {
                                $lead->setLastContactedAt($message->getPublishedAt());
                            }
                        }
                    }
                }
            }
        });
        if ($io) {
            $io->progressFinish();
        }

        $likes = $this->userLikeVehicleRepository->findBy(['value' => 1], ['createdAt' => 'ASC']);
        if ($io) {
            $io->text("Likes...");
            $io->progressStart(count($likes));
        }
        array_walk($likes, function (BaseLikeVehicle $likeVehicle) use (&$leads, $io) {
            if ($io) {
                $io->progressAdvance();
            }
            $liker = $likeVehicle->getUser();
            $seller = $likeVehicle->getVehicle()->getSeller();
            if ($liker instanceof ProUser) {
                if (isset($leads[$liker->getId()]) && isset($leads[$liker->getId()][$seller->getId()])) {
                    $lead = $leads[$liker->getId()][$seller->getId()];
                } else {
                    $lead = $this->getLead($liker, $liker, $seller, false);
                    if ($lead != null) {
                        if (!isset($leads[$liker->getId()])) {
                            $leads[$liker->getId()] = [];
                        }
                        $leads[$liker->getId()][$seller->getId()] = $lead;
                    }
                }
                if ($lead != null) {
                    $lead->increaseNbProLikes();
                    // update createdAt/updateAt when generating lead from old data
                    if ($lead->getCreatedAt() == null || $lead->getCreatedAt() > $likeVehicle->getUpdatedAt()) {
                        $lead->setCreatedAt($likeVehicle->getUpdatedAt());
                    }
                    if ($lead->getLastContactedAt() == null || $lead->getLastContactedAt() < $likeVehicle->getUpdatedAt() ||
                        ($lead->getNbPhoneProActionByPro() + $lead->getNbPhoneProActionByLead() + $lead->getNbPhoneActionByPro() + $lead->getNbPhoneActionByLead() == 0)) {
                        $lead->setLastContactedAt($likeVehicle->getUpdatedAt());
                    }
                }
            }
            if ($seller instanceof ProUser) {
                if (isset($leads[$seller->getId()]) && isset($leads[$seller->getId()][$liker->getId()])) {
                    $lead = $leads[$seller->getId()][$liker->getId()];
                } else {
                    $lead = $this->getLead($seller, $liker, $liker, false);
                    if ($lead != null) {
                        if (!isset($leads[$seller->getId()])) {
                            $leads[$seller->getId()] = [];
                        }
                        $leads[$seller->getId()][$liker->getId()] = $lead;
                    }
                }
                if ($lead != null) {
                    $lead->increaseNbLeadLikes();

                    // update createdAt/lastContactedAt when generating lead from old data
                    if ($lead->getCreatedAt() == null || $lead->getCreatedAt() > $likeVehicle->getUpdatedAt()) {
                        $lead->setCreatedAt($likeVehicle->getUpdatedAt());
                    }
                    if ($lead->getLastContactedAt() == null || $lead->getLastContactedAt() < $likeVehicle->getUpdatedAt() ||
                        ($lead->getNbPhoneProActionByPro() + $lead->getNbPhoneProActionByLead() + $lead->getNbPhoneActionByPro() + $lead->getNbPhoneActionByLead() == 0)) {
                        $lead->setLastContactedAt($likeVehicle->getUpdatedAt());
                    }
                }
            }
        });
        if ($io) {
            $io->progressFinish();
        }

        $results = [];
        if ($io) {
            $io->text("Saving prouser's leads...");
            $io->progressStart(count($leads));
        }
        foreach ($leads as $proUserId => $proUserleads) {
            $io->progressAdvance();
            $this->leadRepository->saveBulk($proUserleads);
            $results[] = [$proUserId, count($proUserleads)];
        }
        if ($io) {
            $io->progressFinish();
        }
        return $results;
    }

    /**
     * Get or create a $proUser's Lead of $userLead
     * @param ProUser $proUser
     * @param BaseUser $initiator
     * @param BaseUser $user
     * @param bool $bddSave if true the new Lead is persisted in the db
     * @return Lead|null null if trying to get himself lead
     */
    public function getLead(ProUser $proUser, BaseUser $initiator, BaseUser $user, bool $bddSave = true): ?Lead
    {
        if ($proUser->is($user)) {
            return null;
        }
        $lead = $this->leadRepository->findOneBy(['proUser' => $proUser, 'userLead' => $user]);
        if ($lead == null) {
            $lead = $this->createLead($proUser, $initiator, $user, $bddSave);
        }
        return $lead;
    }

    /**
     * Create a new $proUser's Lead of $userLead
     * @param ProUser $proUser
     * @param BaseUser $initiator
     * @param BaseUser $userLead
     * @param bool $bddSave if true the new Lead is persisted in the db
     * @return null|Lead null if trying to create himself lead
     */
    public function createLead(ProUser $proUser, BaseUser $initiator, BaseUser $userLead, bool $bddSave = true): ?Lead
    {
        if ($proUser->is($userLead)) {
            return null;
        }
        $lead = new Lead($proUser, $initiator, $userLead);
        if ($bddSave) {
            $this->leadRepository->add($lead);
        }
        return $lead;
    }

    /**
     * Create/Update the lead of the $proUser by incrementing the number of lead messages
     * @param ProUser $proUser
     * @param BaseUser $leadUser
     * @return Lead|null if message to himself
     */
    public function increaseNbLeadMessage(ProUser $proUser, BaseUser $leadUser): ?Lead
    {
        $lead = $this->getLead($proUser, $leadUser, $leadUser, false);
        if ($lead != null) {
            $lead->increaseNbLeadMessages();
            return $this->leadRepository->update($lead);
        }
        return null;
    }

    /**
     * Create/Update the lead of the $proUser by incrementing the number of pro messages
     * @param ProUser $proUser
     * @param BaseUser $leadUser
     * @return Lead|null if message to himself
     */
    public function increaseNbProMessage(ProUser $proUser, BaseUser $leadUser): ?Lead
    {
        $lead = $this->getLead($proUser, $proUser, $leadUser, false);
        if ($lead != null) {
            $lead->increaseNbProMessages();
            return $this->leadRepository->update($lead);
        }
        return null;
    }

    /**
     * Create/Update the lead of the $proUser by incrementing the number of likes by the lead
     * @param ProUser $proUser
     * @param BaseUser $liker
     * @param bool $increase if true then nb of lead likes is increased, otherwise it is decreased
     * @return Lead|null if like to its own vehicle
     */
    public function updateNbLeadLikes(ProUser $proUser, BaseUser $liker, bool $increase = true): ?Lead
    {
        $lead = $this->getLead($proUser, $liker, $liker, false);
        if ($lead != null) {
            $lead->increaseNbLeadLikes($increase ? 1 : -1);
            return $this->leadRepository->update($lead);
        }
        return null;
    }

    /**
     * Create/Update the lead of the $proUser by incrementing the number of likes by the pro
     * @param ProUser $proUser
     * @param BaseUser $liker
     * @param bool $increase if true then nb of pro likes is increased, otherwise it is decreased
     * @return Lead|null if like to its own vehicle
     */
    public function updateNbProLikes(ProUser $proUser, BaseUser $liker, bool $increase = true): ?Lead
    {
        $lead = $this->getLead($proUser, $proUser, $liker, false);
        if ($lead != null) {
            $lead->increaseNbProLikes($increase ? 1 : -1);
            return $this->leadRepository->update($lead);
        }
        return null;
    }

    /**
     * Create/Update the lead of the $proUser by incrementing the number of phone[pro] action by the lead
     * @param ProUser $proUser
     * @param BaseUser $actor
     * @param bool $phonePro If true the phone number concerned is the ProUser.phonePro
     * @return Lead|null if $proUser's own phoneNumber
     */
    public function increaseNbPhoneActionByLead(ProUser $proUser, BaseUser $actor, bool $phonePro): ?Lead
    {
        $lead = $this->getLead($proUser, $actor, $actor, false);
        if ($lead != null) {
            if ($phonePro) {
                $lead->increaseNbPhoneProActionByLead();
            } else {
                $lead->increaseNbPhoneActionByLead();
            }
            return $this->leadRepository->update($lead);
        }
        return null;
    }

    /**
     * Create/Update the lead of the $proUser by incrementing the number of phone[pro] action by the pro
     * @param ProUser $proUser
     * @param BaseUser $actor
     * @param bool $phonePro If true the phone number concerned is the ProUser.phonePro
     * @return Lead|null if $proUser's own phoneNumber
     */
    public function increaseNbPhoneActionByPro(ProUser $proUser, BaseUser $actor, bool $phonePro): ?Lead
    {
        $lead = $this->getLead($proUser, $proUser, $actor, false);
        if ($lead != null) {
            if ($phonePro) {
                $lead->increaseNbPhoneProActionByPro();
            } else {
                $lead->increaseNbPhoneActionByPro();
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