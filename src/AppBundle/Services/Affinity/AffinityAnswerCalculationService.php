<?php

namespace AppBundle\Services\Affinity;


use AppBundle\Doctrine\Entity\AffinityDegree;
use AppBundle\Doctrine\Repository\DoctrineAffinityDegreeRepository;
use Symfony\Component\Translation\TranslatorInterface;
use TypeForm\Doctrine\Entity\AffinityAnswer;
use TypeForm\Doctrine\Entity\AffinityPersonalAnswers;
use TypeForm\Doctrine\Entity\AffinityProAnswers;
use TypeForm\Doctrine\Repository\DoctrineAffinityAnswerRepository;
use Wamcar\User\Enum\FirstContactPreference;
use Wamcar\User\PersonalUser;
use Wamcar\User\Project;
use Wamcar\User\ProUser;
use Wamcar\User\Title;

class AffinityAnswerCalculationService
{
    const PERSONAL_SEARCHED_TITLE_ID = 'fHegKK6nkIG9';
    const PERSONAL_SEARCHED_EXPERIENCE_ID = 'HulaGSXnxphU';
    const PERSONAL_SEARCHED_HOBBIES_ID = 'WZnVUWj4HHsW';
    const PERSONAL_UNIFORM_ID = 'IHyeH2RSn1UZ';
    const PERSONAL_NEW_USED_ID = 'xhmuspS0WiTS';
    const PERSONAL_VEHICLE_USAGE_ID = 'zwog9noLip4v';
    const PERSONAL_USAGE_COMPANY = 'Pour votre société';
    const PERSONALCOMPANY_ACTIVITY_ID = 'AFuqafxfW7Hh';
    const PERSONAL_NB_VEHICLE_ID = 'qfFukJfpOwrl';
    const PERSONAL_HOW_HELP_ID = 'rkxq3BfaQlSN';
    const PERSONAL_VEHICLE_BODY_ID = 'ATQKYy5DpFVk';
    const PERSONAL_GENERATION_ID = 'aWbcaaUVpmxt';
    const PERSONAL_SEATS_NUMBER_ID = 'cfcyC8Nf1Kh3';
    const PERSONAL_STRONG_POINTS_ID = 'SjNmrumq88G4';
    const PERSONAL_IMPROVEMENTS_ID = 'SON0hhB1AcWy';
    const PERSONAL_ENERGY_ID = 'U4Y1BYz7KLyA';
    const PERSONAL_OPTIONS_CHOICE_ID = 'zTphixOBEOb3';
    const PERSONAL_SECURITY_OPTIONS_ID = 'KCVY0k8YaoLc';
    const PERSONAL_CONFORT_OPTIONS_ID = 'Ho3EEXzanIIg';
    const PERSONAL_SEARCHED_ADVICES_ID = 'b6OEruT7GF2F';
    const PERSONAL_BUDGET_ID = 'q8Eh6zRDfIWl';
    const PERSONAL_FIRST_CONTACT_CHANNEL_ID = 'dfdx8wF1Q6uS';
    const PERSONAL_PHONE_NUMBER_ID = 'jjLHWMS98BMJ';
    const PERSONAL_AVAILABILITIES_ID = 'cbz7epXYGnal';
    const PERSONAL_FIRST_CONTACT_PREF_ID = 'EMQUuMj7kjeG';
    const PERSONAL_OTHER_HOBBIES_ID = 'VxsHHmccvxvy';
    const PERSONAL_ROAD_ID = 'smLmytwZXVX0';

    const PRO_MAIN_PROFESSION_ID = 'hBycrRK4i8bu';
    const PRO_COMPANY_SELLER = 'Vendeur sociétés';
    const PRO_EXPERIENCE_ID = 'pXwMCaGPIkYn';
    const PRO_TITLE_ID = 'N20Fa9XvCxe3';
    const PRO_UNIFORM_ID = 'Wn0TWRNf55PM';
    const PRO_HOBBY_ID = 'DoP0ub90B4hM';
    const PRO_HOBBY_LEVEL_ID = 'd7oa1YRfAQXA';
    const PRO_FIRST_CONTACT_CHANNEL_ID = 'QigMnuHRkR1i';
    const PRO_PHONE_NUMBER_ID = 'GxheFoYm4Ai8';
    const PRO_AVAILABILITIES_ID = 'FM79yWzrTYQb';
    const PRO_FIRST_CONTACT_PREF_ID = 'BW5ObrOs8EoD';
    const PRO_PRICES_ID = 'KwwCbCkzNoro';
    const PRO_BRANDS_ID = 'elxoSP08BxCU';
    const PRO_ADVICES_ID = 'OdmhBOcw0Tta';
    const PRO_VEHICLE_BODY_ID = 'Sn72hV3LGlkh';
    const PRO_SUGGESTION_ID = 'U6L50ubnDRU5';
    const PRO_OTHER_HOBBIES_ID = 'm57Ls95xJ5Ca';
    const PRO_ROAD_ID = 'Q7QBuiJTCKDB';

    const AVAILABILITIES_WEEK = 'En semaine';
    const AVAILABILITIES_WEEKEND = 'Le week-end';

    /** @var DoctrineAffinityDegreeRepository $affinityDegreeRepository */
    private $affinityDegreeRepository;

    /** @var DoctrineAffinityAnswerRepository $affinityAnswerRepository */
    private $affinityAnswerRepository;

    /** @var TranslatorInterface $translator */
    private $translator;

    /**
     * AffinityAnswerCalculationService constructor.
     * @param DoctrineAffinityDegreeRepository $affinityDegreeRepository
     * @param DoctrineAffinityAnswerRepository $affinityAnswerRepository
     * @param TranslatorInterface $translator
     */
    public function __construct(DoctrineAffinityDegreeRepository $affinityDegreeRepository, DoctrineAffinityAnswerRepository $affinityAnswerRepository, TranslatorInterface $translator)
    {
        $this->affinityDegreeRepository = $affinityDegreeRepository;
        $this->affinityAnswerRepository = $affinityAnswerRepository;
        $this->translator = $translator;
    }

    /**
     * @param AffinityAnswer $mainAffinityAnswer The main user's answer
     * @param AffinityAnswer $withAffinityAnswer The user's answer to compare with
     * @return array|null array of scores or null if errors
     * scores = [
     *  'affinity' => Total affinity score
     *  'profile' => Profile questions score
     *  'passion' => Passion questions score
     *  'positioning' => Positioning questions score
     *  'atomesCrochus' => AtomesCrochus questions score
     * ]
     */
    public function calculateAffinityValue(AffinityAnswer $mainAffinityAnswer, AffinityAnswer $withAffinityAnswer): ?array
    {
        $scores = null;
        if ($mainAffinityAnswer->getUser() instanceof ProUser && $withAffinityAnswer->getUser() instanceof PersonalUser) {
            $scores = $this->calculateProPersonalAffinityValue($mainAffinityAnswer, $withAffinityAnswer);
        } elseif ($mainAffinityAnswer->getUser() instanceof PersonalUser && $withAffinityAnswer->getUser() instanceof ProUser) {
            // Score Pro = score Personal. If assymetric calculation, implement a different function.
            $scores = $this->calculateProPersonalAffinityValue($withAffinityAnswer, $mainAffinityAnswer);
        }
        if (!is_array($scores)) {
            return null;
        }

        $affinityDegree = new AffinityDegree($mainAffinityAnswer->getUser(), $withAffinityAnswer->getUser(),
            $scores['affinity'],
            $scores['profile'],
            $scores['linking'],
            $scores['passion'],
            $scores['positioning'],
            $scores['atomesCrochus']
        );
        $this->affinityDegreeRepository->update($affinityDegree);
        return $scores;
    }

    /**
     * Calculate the score between a Professional and a Personal
     * @param AffinityAnswer $mainAnswer Professional answer
     * @param AffinityAnswer $withAnswer Personal answer
     * @return array [
     *  'affinity' => Total affinity score
     *  'profile' => Profile questions score
     *  'linking' => Linking questions score
     *  'passion' => Passion questions score
     *  'positioning' => Positioning questions score
     *  'atomesCrochus' => AtomesCrochus questions score
     * ]
     */
    private function calculateProPersonalAffinityValue(AffinityAnswer $mainAnswer, AffinityAnswer $withAnswer): array
    {
        if ($mainAnswer->getAffinityProAnswers() == null or $mainAnswer->getTreatedAt() == null) {
            $this->recordProAnswers($mainAnswer);
        }

        if ($withAnswer->getAffinityPersonalAnswers() == null or $withAnswer->getTreatedAt() == null) {
            $this->recordPersonalAnswers($withAnswer);
        }

        $mainProAnswers = $mainAnswer->getAffinityProAnswers();
        $withPersonalAnswers = $withAnswer->getAffinityPersonalAnswers();
        $scores = [];

        //---------------//
        //--- Profile ---//
        //---------------//
        // Title (homme/femme)
        $profileAffinityScore = $this->calculateTitleScore($mainProAnswers->getTitle(), $withPersonalAnswers->getSearchedTitle());
        // Experience
        $profileAffinityScore += $this->calculateExperienceScore($mainProAnswers->getExperience(), $withPersonalAnswers->getSearchedExperience());
        // uniform
        $profileAffinityScore += $this->calculateUniformScore($mainProAnswers->getUniform(), $withPersonalAnswers->getUniformAsArray());
        // Total profile score
        $scores['profile'] = $profileAffinityScore * 100 / 40.0;

        //----------------------------------//
        //--- Mise en relation (linking) ---//
        //----------------------------------//
        // First contact channel
        $linkingScore = $this->calculateFirstContactChannel($mainProAnswers->getFirstContactChannelAsArray(), $withPersonalAnswers->getFirstContactChannelAsArray());
        // Availability
        $linkingScore += $this->calculateAvailabilitiesScore($mainProAnswers->getAvailabilitiesAsArray(), $withPersonalAnswers->getAvailabilitiesAsArray());
        // First contact preference
        $linkingScore += $this->calculateFirstContactPreferenceScore($mainProAnswers->getFirstContactPref(), $withPersonalAnswers->getFirstContactPref());
        // Total linking score
        $scores['linking'] = $linkingScore * 100 / 40.0;

        //---------------//
        //--- Passion ---//
        //---------------//
        // Hobby
        $passionAffinityScore = $this->calculateHobbyScore($mainProAnswers->getHobby(), $mainProAnswers->getHobbyLevel() ?? 0, $withPersonalAnswers->getSearchedHobbiesAsArray());
        // Pro passion website : juste pour information interne
//        if (isset($mainProAnswers->get ?WebSite? ['DbdnZwCAWaOR'])) {
//            $passionAffinityScore += 10;
//        }
        // Total passion score
        $scores['passion'] = $passionAffinityScore * 100 / 25.0;

        //-------------------//
        //--- Positioning ---//
        //-------------------//
        $maxPositioningScore = 10.0;
        // Pro profession Vs Personal vehicle usage
        $positioningScore = $this->calculateMainAcitivityScore($mainProAnswers->getMainProfession(), $withPersonalAnswers->getVehicleUsage());

        // Project related scores : in hidden fields => askproject === true
        if ($withAnswer->getContentAsArray()['form_response']['hidden']['askproject'] === true) {
            $maxPositioningScore = 50;
            if ($withAnswer->getUser()->getProject() == null) {
                $withAnswer->getUser()->setProject(new Project($withAnswer->getUser()));
            }
            /** @var Project $userProject */
            $userProject = $withAnswer->getUser()->getProject();
            // Price
            $positioningScore += $this->calculatePriceScore($mainProAnswers->getPricesAsArray(), $userProject->getBudget());
            // Advise domains
            $positioningScore += $this->calculateAdvicesScore($mainProAnswers->getAdvicesAsArray(), $withPersonalAnswers->getSearchedAdvicesAsArray());
            // Brands
            $userBrands = [];
            foreach ($userProject->getProjectVehicles() as $projectVehicle) {
                $userBrands[] = strtolower($projectVehicle->getMake());
            }
            $positioningScore += $this->calculateBrandScore($mainProAnswers->getBrandsAsArray(), $userBrands);
            // Vehicle type
            $positioningScore += $this->calculateVehicleBodyScore($mainProAnswers->getVehicleBodyAsArray(), $withPersonalAnswers->getVehicleBodyAsArray());
        }
        $scores['positioning'] = $positioningScore * 100 / $maxPositioningScore;

        //----------------------//
        //--- Atomes Crochus ---//
        //----------------------//
        // Other hobbies
        $atomesCrochusScore = $this->calculateOtherHobbiesScore($mainProAnswers->getOtherHobbiesAsArray(), $withPersonalAnswers->getOtherHobbiesAsArray());
        // Road
        $atomesCrochusScore += $this->calculateRoadScore($mainProAnswers->getRoad(), $withPersonalAnswers->getRoad());
        // Total atomesCrochus score
        $scores['atomesCrochus'] = $atomesCrochusScore * 100 / 40.0;

        // Total affinity score
        $scores['affinity'] = ($scores['profile'] + $scores['passion'] + $scores['linking'] +
                $scores['positioning'] + $scores['atomesCrochus']) / 5.0;
        return $scores;
    }


    /**
     * @param PersonalUser $personalUser
     */
    public function updateProUserInformation(ProUser $proUser)
    {
        if ($proUser->getAffinityAnswer() != null) {
            $userAnswerAsArray = $proUser->getAffinityAnswer()->getContentAsArray();
            $userQuestionsAnswers = $this->transformAnswerIntoQuestionsAnswers($userAnswerAsArray['form_response']['answers']);

            // Title (genre)
            if (!empty($userQuestionsAnswers[self::PRO_TITLE_ID])) {
                if ($userQuestionsAnswers[self::PRO_TITLE_ID] == 'Une femme') {
                    $proUser->getUserProfile()->setTitle(Title::MS());
                } else {
                    $proUser->getUserProfile()->setTitle(Title::MR());
                }
            }
            // Préférence de 1er contact
            if (!empty($userQuestionsAnswers[self::PRO_FIRST_CONTACT_PREF_ID])) {
                if ($userQuestionsAnswers[self::PRO_FIRST_CONTACT_PREF_ID] == "que l'on vienne à vous") {
                    $proUser->setFirstContactPreference(FirstContactPreference::I_M_WAITING());
                } elseif ($userQuestionsAnswers[self::PRO_FIRST_CONTACT_PREF_ID] == "faire le premier pas") {
                    $proUser->setFirstContactPreference(FirstContactPreference::I_WILL_BEGIN());
                }
            }

            // Phone number if contact SMS or call
            if (!empty($userQuestionsAnswers[self::PRO_PHONE_NUMBER_ID])) {
                $proUser->setPhonePro($userQuestionsAnswers[self::PRO_PHONE_NUMBER_ID]);
            }

            //-----------------------//
            // Description du profil //
            //-----------------------//
            // Actual description
            $profilDescription = empty($proUser->getDescription()) ? '' : $proUser->getDescription() . PHP_EOL . PHP_EOL;

            // Main profession
            if (!empty($userQuestionsAnswers[self::PRO_MAIN_PROFESSION_ID])) {
                $profilDescription .= $this->translator->trans('user.project.prefill.profesional.description.main_profession',
                    ['%profession%' => lcfirst($userQuestionsAnswers[self::PRO_MAIN_PROFESSION_ID])]);
            }
            // Experience
            if (!empty($userQuestionsAnswers[self::PRO_EXPERIENCE_ID])) {
                $profilDescription .= $this->translator->trans('user.project.prefill.profesional.description.experience',
                    ['%experience%' => strtolower($userQuestionsAnswers[self::PRO_EXPERIENCE_ID])]);
            }
            // Hobby and level
            if (!empty($userQuestionsAnswers[self::PRO_HOBBY_ID]) && !empty($userQuestionsAnswers[self::PRO_HOBBY_LEVEL_ID]) &&
                ($userQuestionsAnswers[self::PRO_HOBBY_LEVEL_ID] ?? 0) >= 3) {
                $profilDescription .= $this->translator->trans('user.project.prefill.profesional.description.hobby',
                    ['%hobby%' => strtolower($userQuestionsAnswers[self::PRO_HOBBY_ID])]);
            }
            // Advices
            if (!empty($userQuestionsAnswers[self::PRO_ADVICES_ID])) {
                $profilDescription .= PHP_EOL . $this->translator->trans('user.project.prefill.profesional.description.advices',
                        ['%advices%' => $userQuestionsAnswers[self::PRO_ADVICES_ID]]);
            }

            // Brands
            if (!empty($userQuestionsAnswers[self::PRO_BRANDS_ID])) {
                $profilDescription .= PHP_EOL . $this->translator->trans('user.project.prefill.profesional.description.brands',
                        ['%brands' => join(', ', $userQuestionsAnswers[self::PRO_BRANDS_ID])]);
            }

            // Vehicle bodies
            if (!empty($userQuestionsAnswers[self::PRO_VEHICLE_BODY_ID])) {
                $profilDescription .= PHP_EOL . $this->translator->trans('user.project.prefill.profesional.description.vehicle_body',
                        ['%vehicle_bodies%' => join(', ', array_map('strtolower', $userQuestionsAnswers[self::PRO_VEHICLE_BODY_ID]))]);
            }

            // First contact preference
            if (!empty($userQuestionsAnswers[self::PRO_FIRST_CONTACT_PREF_ID])) {
                if ($userQuestionsAnswers[self::PRO_FIRST_CONTACT_PREF_ID] == "que l'on vienne à vous") {
                    $profilDescription .= PHP_EOL . $this->translator->trans('user.project.prefill.profesional.description.first_contact_preference.waiting');
                }
            }

            // Suggestions
            if (!empty($userQuestionsAnswers[self::PRO_SUGGESTION_ID])) {
                $profilDescription .= PHP_EOL . PHP_EOL . $userQuestionsAnswers[self::PRO_SUGGESTION_ID];
            }

            $proUser->getUserProfile()->setDescription($profilDescription);
        }
    }

    /**
     * @param PersonalUser $personalUser
     */
    public function updatePersonalUserInformation(PersonalUser $personalUser)
    {
        if ($personalUser->getAffinityAnswer() != null) {
            $userAnswerAsArray = $personalUser->getAffinityAnswer()->getContentAsArray();
            $userQuestionsAnswers = $this->transformAnswerIntoQuestionsAnswers($userAnswerAsArray['form_response']['answers']);

            if ($personalUser->getProject() === null) {
                $personalUser->setProject(new Project($personalUser));
            }

            // Disponibilités
            $disponibiliteId = self::PERSONAL_AVAILABILITIES_ID;
            if (!empty($userQuestionsAnswers[$disponibiliteId])) {
                if (is_array($userQuestionsAnswers[$disponibiliteId])) {
                    $personalUser->setContactAvailabilities(json_encode($userQuestionsAnswers[$disponibiliteId]));
                }
            }

            // Préférence de 1er contact
            if (!empty($userQuestionsAnswers[self::PERSONAL_FIRST_CONTACT_PREF_ID])) {
                if ($userQuestionsAnswers[self::PERSONAL_FIRST_CONTACT_PREF_ID] == "que l'on vienne à vous") {
                    $personalUser->setFirstContactPreference(FirstContactPreference::I_M_WAITING());
                } elseif ($userQuestionsAnswers[self::PERSONAL_FIRST_CONTACT_PREF_ID] == "faire le premier pas") {
                    $personalUser->setFirstContactPreference(FirstContactPreference::I_WILL_BEGIN());
                }
            }

            // Phone number if contact SMS or call
            if (!empty($userQuestionsAnswers[self::PERSONAL_PHONE_NUMBER_ID])) {
                $personalUser->getUserProfile()->setPhone($userQuestionsAnswers[self::PERSONAL_PHONE_NUMBER_ID]);
            }

            //-----------------------//
            // Description du projet //
            //-----------------------//
            // Actual description
            $projectDescription = empty($personalUser->getProject()->getDescription()) ? '' : $personalUser->getProject()->getDescription() . PHP_EOL . PHP_EOL;

            // New / Used
            if (!empty($userQuestionsAnswers[self::PERSONAL_NEW_USED_ID])) {
                $projectDescription .= $this->translator->trans('user.project.prefill.personal.description.search') . strtolower($userQuestionsAnswers[self::PERSONAL_NEW_USED_ID]);
            }
            // Usage of vehicle (personal or company)
            if (!empty($userQuestionsAnswers[self::PERSONAL_VEHICLE_USAGE_ID])) {
                if (empty($userQuestionsAnswers[self::PERSONAL_NEW_USED_ID])) {
                    $projectDescription .= $this->translator->trans('user.project.prefill.personal.description.search');
                } else {
                    $projectDescription .= ' ';
                }
                if ($userQuestionsAnswers[self::PERSONAL_VEHICLE_USAGE_ID] == self::PERSONAL_USAGE_COMPANY) {
                    $projectDescription .= $this->translator->trans('user.project.prefill.personal.description.vehicle_usage.company');

                    if (!empty($userQuestionsAnswers[self::PERSONALCOMPANY_ACTIVITY_ID]) || !empty($userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID])) {
                        $projectDescription .= PHP_EOL;

                        // Activity
                        if (!empty($userQuestionsAnswers[self::PERSONALCOMPANY_ACTIVITY_ID])) {
                            $projectDescription .= $this->translator->trans('user.project.prefill.personal.description.company.activity',
                                ['%activity%' => strtolower($userQuestionsAnswers[self::PERSONALCOMPANY_ACTIVITY_ID])]);
                        }
                        // and
                        if (!empty($userQuestionsAnswers[self::PERSONALCOMPANY_ACTIVITY_ID]) && !empty($userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID])) {
                            $projectDescription .= $this->translator->trans('user.project.prefill.personal.description.company.and');
                        }
                        // Nb of vehicle
                        if (!empty($userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID])) {
                            $projectDescription .= $this->translator->transChoice('user.project.prefill.personal.description.company.nb_vehicle',
                                ($userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID] == "Plus de 10" ? 11 : $userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID]),
                                ['%nb_vehicle%' => ($userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID] == "Plus de 10" ? strtolower($userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID]) : intval($userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID]))]);
                        }
                    }
                } else {
                    $projectDescription .= $this->translator->trans('user.project.prefill.personal.description.vehicle_usage.personal');
                }
            }

            if (!empty($userQuestionsAnswers[self::PERSONAL_HOW_HELP_ID])) {
                $projectDescription .= PHP_EOL . $userQuestionsAnswers[self::PERSONAL_HOW_HELP_ID] . '.';
            }

            // Vehicle body
            if (!empty($userQuestionsAnswers[self::PERSONAL_VEHICLE_BODY_ID])) {
                $projectDescription .= PHP_EOL;
                $projectDescription .= $this->translator->trans('user.project.prefill.personal.description.search_a');
                $projectDescription .= " " . join(', ', $userQuestionsAnswers[self::PERSONAL_VEHICLE_BODY_ID]);
            }

            // Energy
            if (!empty($userQuestionsAnswers[self::PERSONAL_ENERGY_ID])) {
                if (empty($userQuestionsAnswers[self::PERSONAL_VEHICLE_BODY_ID])) {
                    $projectDescription .= PHP_EOL;
                    $projectDescription .= $this->translator->trans('user.project.prefill.personal.description.search_a');
                } else {
                    $projectDescription .= ', ';
                }
                $projectDescription .= join(', ', array_map('strtolower', $userQuestionsAnswers[self::PERSONAL_ENERGY_ID]));
            }

            // Generation
            if (!empty($userQuestionsAnswers[self::PERSONAL_GENERATION_ID])) {
                if (empty($userQuestionsAnswers[self::PERSONAL_VEHICLE_BODY_ID]) && empty($userQuestionsAnswers[self::PERSONAL_ENERGY_ID])) {
                    $projectDescription .= PHP_EOL;
                    $projectDescription .= $this->translator->trans('user.project.prefill.personal.description.search_a');
                } else {
                    $projectDescription .= ', ';
                }
                $projectDescription .= $this->translator->trans('user.project.prefill.personal.description.generation',
                    ['%generation%' => join(', ', array_map('strtolower', $userQuestionsAnswers[self::PERSONAL_GENERATION_ID]))]);
            }

            // Seats number
            if (!empty($userQuestionsAnswers[self::PERSONAL_SEATS_NUMBER_ID])) {
                $projectDescription .= PHP_EOL . $this->translator->trans('user.project.prefill.personal.description.seats_number',
                        ['%seats_number%' => $userQuestionsAnswers[self::PERSONAL_SEATS_NUMBER_ID]]);
            }

            // Strong points
            if (!empty($userQuestionsAnswers[self::PERSONAL_STRONG_POINTS_ID])) {
                $projectDescription .= PHP_EOL . $this->translator->trans('user.project.prefill.personal.description.strong_points',
                        ['%strong_points%' => join(', ', $userQuestionsAnswers[self::PERSONAL_STRONG_POINTS_ID])]);
            }

            // Improvements
            if (!empty($userQuestionsAnswers[self::PERSONAL_IMPROVEMENTS_ID])) {
                $projectDescription .= PHP_EOL . $this->translator->trans('user.project.prefill.personal.description.improvements',
                        ['%improvements%' => join(', ', $userQuestionsAnswers[self::PERSONAL_IMPROVEMENTS_ID])]);
            }

            // Options
            if (!empty($userQuestionsAnswers[self::PERSONAL_OPTIONS_CHOICE_ID])
                && strtolower($userQuestionsAnswers[self::PERSONAL_OPTIONS_CHOICE_ID]) == "choix des options") {
                // Security options
                if (!empty($userQuestionsAnswers[self::PERSONAL_SECURITY_OPTIONS_ID])) {
                    $projectDescription .= PHP_EOL . $this->translator->trans('user.project.prefill.personal.description.options.security',
                            ['%security_options%' => join(', ', $userQuestionsAnswers[self::PERSONAL_SECURITY_OPTIONS_ID])]);
                }
                // Confort options
                if (!empty($userQuestionsAnswers[self::PERSONAL_CONFORT_OPTIONS_ID])) {
                    $projectDescription .= PHP_EOL . $this->translator->trans('user.project.prefill.personal.description.options.confort',
                            ['%confort_options%' => join(', ', $userQuestionsAnswers[self::PERSONAL_CONFORT_OPTIONS_ID])]);
                }
            }
            // Conseils attendus
            if (!empty($userQuestionsAnswers[self::PERSONAL_SEARCHED_ADVICES_ID])) {
                // Searched advices
                $projectDescription .= PHP_EOL . $this->translator->trans('user.project.prefill.personal.description.searched_advices',
                        ['%search_advices%' => join(', ', array_map("strtolower", $userQuestionsAnswers[self::PERSONAL_SEARCHED_ADVICES_ID]))]);
            }

            $personalUser->getProject()->setDescription($projectDescription);

            // Budget global
            if (!empty($userQuestionsAnswers[self::PERSONAL_BUDGET_ID])) {
                $personalUser->getProject()->setBudget($userQuestionsAnswers[self::PERSONAL_BUDGET_ID]);
            }

            // Type de recherche : véhicule unique / flotte
            if (!empty($userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID])) {
                $personalUser->getProject()->setIsFleet(($userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID] > 1 || $userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID] == "Plus de 10"));
            }
        }
    }

    /**
     * Transform format of response from the JSON to array with question Id as key, and answer as value. The format of
     * the value depends on the question type : string or array (multiple choices question)
     * @param array $answers
     * @return array
     */
    private function transformAnswerIntoQuestionsAnswers(array $answers): array
    {
        $questionsAnswers = [];
        foreach ($answers as $answer) {
            if (in_array($answer['type'], ['text', 'number', 'url'])) {
                $questionsAnswers[$answer['field']['id']] = $answer[$answer['type']];
            } elseif ($answer['type'] == 'choice') {
                if (isset($answer[$answer['type']]['label'])) {
                    $questionsAnswers[$answer['field']['id']] = $answer[$answer['type']]['label'];
                } elseif ($answer[$answer['type']]['other']) {
                    $questionsAnswers[$answer['field']['id']] = $answer[$answer['type']]['other'];
                }
            } elseif ($answer['type'] == 'choices') {
                $labelAnswers = [];
                if (isset($answer[$answer['type']]['labels'])) {
                    $labelAnswers = $answer[$answer['type']]['labels'];
                }
                if (isset($answer[$answer['type']]['other'])) {
                    $labelAnswers[] = $answer[$answer['type']]['other'];
                }
                $questionsAnswers[$answer['field']['id']] = $labelAnswers;
            }
        }
        return $questionsAnswers;
    }

    /**
     * Create or update the AffinityProAnswers of a AffinityAnswer
     * @param AffinityAnswer $affinityAnswer
     * @return AffinityAnswer
     */
    private function recordProAnswers(AffinityAnswer $affinityAnswer): AffinityAnswer
    {
        $affinityProAnswers = $affinityAnswer->getAffinityProAnswers();
        if ($affinityProAnswers == null) {
            $affinityProAnswers = new AffinityProAnswers($affinityAnswer);
        }

        $questionsAnswers = $this->transformAnswerIntoQuestionsAnswers($affinityAnswer->getContentAsArray()['form_response']['answers']);

        $affinityProAnswers->setTitle($questionsAnswers[self::PRO_TITLE_ID] ?? null);
        $affinityProAnswers->setMainProfession($questionsAnswers[self::PRO_MAIN_PROFESSION_ID] ?? null);
        $affinityProAnswers->setExperience($questionsAnswers[self::PRO_EXPERIENCE_ID] ?? null);
        $affinityProAnswers->setUniform($questionsAnswers[self::PRO_UNIFORM_ID] ?? null);
        $affinityProAnswers->setHobby($questionsAnswers[self::PRO_HOBBY_ID] ?? null);
        $affinityProAnswers->setHobbyLevel($questionsAnswers[self::PRO_HOBBY_LEVEL_ID] ?? null);
        $affinityProAnswers->setAdvices(json_encode($questionsAnswers[self::PRO_ADVICES_ID] ?? null));
        $affinityProAnswers->setVehicleBody(json_encode($questionsAnswers[self::PRO_VEHICLE_BODY_ID] ?? null));
        $affinityProAnswers->setBrands(json_encode($questionsAnswers[self::PRO_BRANDS_ID] ?? null));
        $affinityProAnswers->setFirstContactChannel(json_encode($questionsAnswers[self::PRO_FIRST_CONTACT_CHANNEL_ID] ?? null));
        $affinityProAnswers->setPhoneNumber($questionsAnswers[self::PRO_PHONE_NUMBER_ID] ?? null);
        $affinityProAnswers->setAvailabilities(json_encode($questionsAnswers[self::PRO_AVAILABILITIES_ID] ?? null));
        $affinityProAnswers->setFirstContactPref($questionsAnswers[self::PRO_FIRST_CONTACT_PREF_ID] ?? null);
        $affinityProAnswers->setSuggestion($questionsAnswers[self::PRO_SUGGESTION_ID] ?? null);
        $affinityProAnswers->setPrices(json_encode($questionsAnswers[self::PRO_PRICES_ID] ?? null));
        $affinityProAnswers->setOtherHobbies(json_encode($questionsAnswers[self::PRO_OTHER_HOBBIES_ID] ?? null));
        $affinityProAnswers->setRoad($questionsAnswers[self::PRO_ROAD_ID] ?? null);

        $this->affinityAnswerRepository->update($affinityAnswer);
        return $affinityAnswer;
    }


    private function recordPersonalAnswers(AffinityAnswer $affinityAnswer): AffinityAnswer
    {
        $affinityPersonalAnswers = $affinityAnswer->getAffinityPersonalAnswers();
        if ($affinityPersonalAnswers == null) {
            $affinityPersonalAnswers = new AffinityPersonalAnswers($affinityAnswer);
        }

        $questionsAnswers = $this->transformAnswerIntoQuestionsAnswers($affinityAnswer->getContentAsArray()['form_response']['answers']);

        $affinityPersonalAnswers->setBudget($questionsAnswers[self::PERSONAL_BUDGET_ID] ?? null);
        $affinityPersonalAnswers->setSearchedAdvices(json_encode($questionsAnswers[self::PERSONAL_SEARCHED_ADVICES_ID] ?? null));
        $affinityPersonalAnswers->setNewUsed($questionsAnswers[self::PERSONAL_NEW_USED_ID] ?? null);
        $affinityPersonalAnswers->setVehicleUsage($questionsAnswers[self::PERSONAL_VEHICLE_USAGE_ID] ?? null);
        $affinityPersonalAnswers->setPersonalCompanyActivity($questionsAnswers[self::PERSONAL_USAGE_COMPANY] ?? null);
        $affinityPersonalAnswers->setHowHelp($questionsAnswers[self::PERSONAL_HOW_HELP_ID] ?? null);
        $affinityPersonalAnswers->setGeneration(json_encode($questionsAnswers[self::PERSONAL_GENERATION_ID] ?? null));
        $affinityPersonalAnswers->setVehicleBody(json_encode($questionsAnswers[self::PERSONAL_VEHICLE_BODY_ID] ?? null));
        $affinityPersonalAnswers->setEnergy(json_encode($questionsAnswers[self::PERSONAL_ENERGY_ID] ?? null));
        $affinityPersonalAnswers->setSeatsNumber($questionsAnswers[self::PERSONAL_SEATS_NUMBER_ID] ?? null);
        $affinityPersonalAnswers->setStrongPoints(json_encode($questionsAnswers[self::PERSONAL_STRONG_POINTS_ID] ?? null));
        $affinityPersonalAnswers->setImprovements(json_encode($questionsAnswers[self::PERSONAL_IMPROVEMENTS_ID] ?? null));
        $affinityPersonalAnswers->setSecurityOptions(json_encode($questionsAnswers[self::PERSONAL_SECURITY_OPTIONS_ID] ?? null));
        $affinityPersonalAnswers->setConfortOptions(json_encode($questionsAnswers[self::PERSONAL_CONFORT_OPTIONS_ID] ?? null));
        $affinityPersonalAnswers->setOptionsChoice($questionsAnswers[self::PERSONAL_OPTIONS_CHOICE_ID] ?? null);
        $affinityPersonalAnswers->setSearchedHobbies(json_encode($questionsAnswers[self::PERSONAL_SEARCHED_HOBBIES_ID] ?? null));
        $affinityPersonalAnswers->setSearchedTitle($questionsAnswers[self::PERSONAL_SEARCHED_TITLE_ID] ?? null);
        $affinityPersonalAnswers->setSearchedExperience($questionsAnswers[self::PERSONAL_SEARCHED_EXPERIENCE_ID] ?? null);
        $affinityPersonalAnswers->setUniform(json_encode($questionsAnswers[self::PERSONAL_UNIFORM_ID] ?? null));
        $affinityPersonalAnswers->setFirstContactChannel(json_encode($questionsAnswers[self::PERSONAL_FIRST_CONTACT_CHANNEL_ID] ?? null));
        $affinityPersonalAnswers->setPhoneNumber($questionsAnswers[self::PERSONAL_PHONE_NUMBER_ID] ?? null);
        $affinityPersonalAnswers->setAvailabilities(json_encode($questionsAnswers[self::PERSONAL_AVAILABILITIES_ID] ?? null));
        $affinityPersonalAnswers->setFirstContactPref($questionsAnswers[self::PERSONAL_FIRST_CONTACT_PREF_ID] ?? null);
        $affinityPersonalAnswers->setotherHobbies(json_encode($questionsAnswers[self::PERSONAL_OTHER_HOBBIES_ID] ?? null));
        $affinityPersonalAnswers->setRoad($questionsAnswers[self::PERSONAL_ROAD_ID] ?? null);

        $this->affinityAnswerRepository->update($affinityAnswer);
        return $affinityAnswer;
    }

    /**
     * Calculate the score about Title
     * @param string|null $proAnswer
     * @param string|null $personalAnswer
     * @return float
     */
    private function calculateTitleScore(?string $proAnswer, ?string $personalAnswer): float
    {
        if (strtolower($proAnswer) == strtolower($personalAnswer) || strtolower($personalAnswer) === "peu importe" || $personalAnswer == null) {
            return 10;
        }
        return 0;
    }

    /**
     * Calculate the score about Experience
     * @param string|null $proAnswer
     * @param string|null $personalAnswer
     * @return float
     */
    private function calculateExperienceScore(?string $proAnswer, ?string $personalAnswer): float
    {
        // Ne pas mettre de break est intentionnel
        switch (strtolower($personalAnswer)) {
            case "au moins 2 ans":
                if ($proAnswer === "2 à 5 ans") {
                    return 15;
                }
            case "au moins 5 ans":
                if ($proAnswer === "5 à 10 ans") {
                    return 15;
                }
            case "au moins 10 ans":
                if ($proAnswer === "10 à 20 ans") {
                    return 15;
                }
            case "au moins 20 ans":
                if ($proAnswer === "Plus de 20 ans") {
                    return 15;
                }
                break;
            case "peu importe":
            case null:
                return 15;
        }
        return 0;
    }

    /**
     * Calculate the score about Uniform
     * @param string|null $proAnswer
     * @param array $personalAnswer
     * @return float
     */
    private function calculateUniformScore(?string $proAnswer, array $personalAnswer): float
    {
        if (empty($personalAnswer) || in_array('Sans avis', $personalAnswer)) {
            return 15;
        }
        if (in_array($proAnswer, $personalAnswer)) {
            return 15;
        }
        return 0;
    }

    /**
     * Calculate the score about the first contact channel
     * @param array $proAnswer
     * @param array $ersonnalAnswer
     * @return float
     */
    private function calculateFirstContactChannel(array $proAnswer, array $personalAnswer): float
    {
        if (empty($personalAnswer)) {
            return 10;
        }
        if (!empty(array_intersect($proAnswer, $personalAnswer))) {
            return 10;
        }
        return 0;
    }

    /**
     * Calculate the score about availavilities
     * @param array $proAnswer
     * @param array $personalAnswer
     * @return float
     */
    private function calculateAvailabilitiesScore(array $proAnswer, array $personalAnswer): float
    {
        if (empty($personalAnswer)) {
            return 10;
        }
        if (!empty($personalAnswer)) {
            return 20;
        }
        if ((in_array(self::AVAILABILITIES_WEEK, $proAnswer) && in_array(self::AVAILABILITIES_WEEK, $personalAnswer))
            || (in_array(self::AVAILABILITIES_WEEKEND, $proAnswer) && in_array(self::AVAILABILITIES_WEEKEND, $personalAnswer))
        ) {
            $nbCommon = 0;
            foreach ($personalAnswer[self::PERSONAL_AVAILABILITIES_ID] as $availibility) {
                if ($availibility != self::AVAILABILITIES_WEEK && $availibility != self::AVAILABILITIES_WEEKEND) {
                    if (in_array($availibility, $proAnswer)) {
                        $nbCommon++;
                    }
                }
            }
        }
        return min(20, 10 * $nbCommon);
    }

    /**
     * Calculate the score about first contact preference
     * @param null|string $proAnswer
     * @param null|string $personalAnswer
     * @return float
     */
    private function calculateFirstContactPreferenceScore(?string $proAnswer, ?string $personalAnswer): float
    {
        if (empty($personalAnswer)) {
            return 10;
        }
        if ($proAnswer == $personalAnswer) {
            return 10;
        }
        return 0;
    }

    /**
     * Calculate the score about pro hobby
     * @param string $proAnswer
     * @param int $levelProAnswer
     * @param array $personalAnswer
     * @return float
     */
    private function calculateHobbyScore(?string $proAnswer, int $levelProAnswer = 0, array $personalAnswer): float
    {
        if (empty($personalAnswer) || in_array("Non, pas vraiment", $personalAnswer)) {
            return 25;
        } elseif ($proAnswer != null && in_array($proAnswer, $personalAnswer)) {
            return 15 + $levelProAnswer * 2;
        }
        return 0;
    }

    /**
     * Calculate the score about main pro profession and personal usage
     * @param null|string $proAnswer
     * @param null|string $personalAnswer
     * @return float
     */
    private function calculateMainAcitivityScore(?string $proAnswer, ?string $personalAnswer): float
    {
        if (empty($personalAnswer) || ($proAnswer == self::PRO_COMPANY_SELLER && $personalAnswer == self:: PERSONAL_USAGE_COMPANY)
            || ($proAnswer != self::PRO_COMPANY_SELLER && $personalAnswer != self:: PERSONAL_USAGE_COMPANY)) {
            return 10;
        }
        return 0;
    }

    /**
     * Calculate the score about prices
     * @param array|null $proAnswer
     * @param int|null $personalAnswer
     * @return float
     */
    private function calculatePriceScore(array $proAnswer, ?int $personalAnswer): float
    {
        if ($personalAnswer === null) {
            return 10;
        }
        $score = 0;
        foreach ($proAnswer as $priceRange) {
            switch ($priceRange) {
                case "Moins de 5 000 €":
                    if ($personalAnswer <= 5000) {
                        $score = 10;
                    }
                    break;
                case "5 000 € à 10 000 €":
                    if (5000 <= $personalAnswer && $personalAnswer <= 10000) {
                        $score = 10;
                    }
                    break;
                case "10 000 à 20 000 €":
                    if (10000 <= $personalAnswer && $personalAnswer <= 20000) {
                        $score = 10;
                    }
                    break;
                case "20 000 à 40 000 €":
                    if (20000 <= $personalAnswer && $personalAnswer <= 40000) {
                        $score = 10;
                    }
                    break;
                case "40 000 à 70 000 €":
                    if (40000 <= $personalAnswer && $personalAnswer <= 70000) {
                        $score = 10;
                    }
                    break;
                case "Plus de 70 000 €":
                    if (70000 <= $personalAnswer) {
                        $score = 10;
                    }
                    break;
            }
            if ($score > 0) {
                break;
            }
        }
        return max(0, $score - (count($proAnswer) - 1) * 2);
    }

    /**
     * Calculate the score about Advices
     * @param array $proAnswer
     * @param array $personalAnswer
     * @return float
     */
    private function calculateAdvicesScore(array $proAnswer, array $personalAnswer): float
    {
        return $this->formule1($proAnswer, $personalAnswer, 10);
    }

    /**
     * Calculate the score about Brands
     * @param array $proAnswer
     * @param array $personalAnswer
     * @return float
     */
    private function calculateBrandScore(array $proAnswer, array $personalAnswer): float
    {
        return $this->formule1(array_map('strtolower', $proAnswer), $personalAnswer, 10);
    }

    /**
     * Calculate the score about the vehicle types
     * @param array $proAnswer
     * @param array $personalAnswer
     * @return float
     */
    private function calculateVehicleBodyScore(array $proAnswer, array $personalAnswer): float
    {
        return $this->formule1($proAnswer, $personalAnswer, 10);
    }

    /**
     * Calculate the score about other hobbies
     * @param array $proAnswer
     * @param array $personalAnswer
     * @return float
     */
    private function calculateOtherHobbiesScore(array $proAnswer, array $personalAnswer): float
    {
        return $this->formule1($proAnswer, $personalAnswer, 25);
    }

    /**
     * Calculate the score about the road
     * @param string|null $proAnswer
     * @param string|null $personalAnswer
     * @return float
     */
    private function calculateRoadScore(?string $proAnswer, ?string $personalAnswer): float
    {
        if ($personalAnswer === null || ($proAnswer != null && strtolower($proAnswer) === strtolower($personalAnswer))) {
            return 15;
        }

        return 0;
    }

    /**
     * Calculate a score between two answers that are sets of selections.
     *
     * @param array $proAnswers
     * @param array $personalAnswers
     * @param int $maxPoint
     * @return float
     */
    private function formule1(array $proAnswers, array $personalAnswers, int $maxPoint): float
    {
        if (empty($personalAnswers)) {
            return $maxPoint;
        }
        if (empty($proAnswers)) {
            return 0;
        }
        $intersection = array_intersect($proAnswers, $personalAnswers);
        return $maxPoint * count($intersection) / count($proAnswers);
    }
}