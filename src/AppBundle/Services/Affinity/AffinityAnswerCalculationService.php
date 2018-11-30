<?php

namespace AppBundle\Services\Affinity;


use AppBundle\Doctrine\Entity\AffinityDegree;
use AppBundle\Doctrine\Repository\DoctrineAffinityDegreeRepository;
use Symfony\Component\Translation\TranslatorInterface;
use TypeForm\Doctrine\Entity\AffinityAnswer;
use Wamcar\User\Enum\FirstContactPreference;
use Wamcar\User\PersonalUser;
use Wamcar\User\Project;
use Wamcar\User\ProUser;
use Wamcar\User\Title;

class AffinityAnswerCalculationService
{

    const POURCENTAGE_SCORE_PROFILE = 0.25;
    const POURCENTAGE_SCORE_PASSION = 0.25;
    const POURCENTAGE_SCORE_POSITIONING = 0.25;
    const POURCENTAGE_SCORE_ATOMES = 0.25;

    const PERSONAL_BUDGET_ID = 'q8Eh6zRDfIWl';
    const PERSONAL_SEARCHED_ADVICES_ID = 'b6OEruT7GF2F';
    const PERSONAL_NEW_USED_ID = 'xhmuspS0WiTS';
    const PERSONAL_USAGE_ID = 'zwog9noLip4v';
    const PERSONAL_NB_VEHICLE_ID = 'qfFukJfpOwrl';
    const PERSONALCOMPANY_ACTIVITY_ID = 'AFuqafxfW7Hh';
    const PERSONAL_HOW_HELP_ID = 'rkxq3BfaQlSN';
    const PERSONAL_GENERATION_ID = 'e07baxj9XuDU';
    const PERSONAL_VEHICLE_BODY_ID = 'IboKjSUiGI9I';
    const PERSONAL_ENERGY_ID = 'RadCV7IufAGN';
    const PERSONAL_SEATS_NUMBER_ID = 'cfcyC8Nf1Kh3';
    const PERSONAL_STRONG_POINTS_ID = 'SjNmrumq88G4';
    const PERSONAL_IMPROVEMENTS_ID = 'SON0hhB1AcWy';
    const PERSONAL_SECURITY_OPTIONS_ID = 'KCVY0k8YaoLc';
    const PERSONAL_CONFORT_OPTIONS_ID = 'Ho3EEXzanIIg';
    const PERSONAL_OPTIONS_CHOICE_ID = 'zTphixOBEOb3';
    const PERSONAL_SEARCHED_HOBBIES_ID = 'WZnVUWj4HHsW';
    const PERSONAL_SEARCHED_TITLE_ID = 'fHegKK6nkIG9';
    const PERSONAL_SEARCHED_EXPERIENCE_ID = 'HulaGSXnxphU';

    const PRO_TITLE_ID = 'N20Fa9XvCxe3';
    const PRO_MAIN_PROFESSION_ID = 'hBycrRK4i8bu';
    const PRO_EXPERIENCE_ID = 'pXwMCaGPIkYn';
    const PRO_HOBBY_ID = 'DoP0ub90B4hM';
    const PRO_HOBBY_LEVEL_ID = 'd7oa1YRfAQXA';
    const PRO_ADVICES_ID = 'Gqzy1hTJ6ksm';
    const PRO_VEHICLE_BODY_ID = 'Sn72hV3LGlkh';
    const PRO_BRANDS_ID = 'elxoSP08BxCU';
    const PRO_FIRST_CONTACT_PREF_ID = 'BW5ObrOs8EoD';
    const PRO_SUGGESTION_ID = 'U6L50ubnDRU5';

    /** @var DoctrineAffinityDegreeRepository $affinityDegreeRepository */
    private $affinityDegreeRepository;

    /** @var TranslatorInterface $translator */
    private $translator;

    /**
     * AffinityAnswerCalculationService constructor.
     * @param DoctrineAffinityDegreeRepository $affinityDegreeRepository
     * @param TranslatorInterface $translator
     */
    public function __construct(DoctrineAffinityDegreeRepository $affinityDegreeRepository, TranslatorInterface $translator)
    {
        $this->affinityDegreeRepository = $affinityDegreeRepository;
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
            $scores = $this->calculateProPersonalAffinityValue($mainAffinityAnswer->getContentAsArray(), $withAffinityAnswer->getContentAsArray());
        } elseif ($mainAffinityAnswer->getUser() instanceof PersonalUser && $withAffinityAnswer->getUser() instanceof ProUser) {
            // Score Pro = score Personal. If assymetric calculation, implement a different function.
            $scores = $this->calculateProPersonalAffinityValue($withAffinityAnswer->getContentAsArray(), $mainAffinityAnswer->getContentAsArray());
        }
        if (!is_array($scores)) {
            return null;
        }

        $affinityDegree = new AffinityDegree($mainAffinityAnswer->getUser(), $withAffinityAnswer->getUser(),
            $scores['affinity'],
            $scores['profile'],
            $scores['passion'],
            $scores['positioning'],
            $scores['atomesCrochus']
        );
        $this->affinityDegreeRepository->update($affinityDegree);
        return $scores;
    }

    /**
     * Calculate the score between a Professional and a Personal
     * @param array $mainAllAnswers Content of professional answer
     * @param array $withAllAnswers Content of personal answer
     * @return array [
     *  'affinity' => Total affinity score
     *  'profile' => Profile questions score
     *  'passion' => Passion questions score
     *  'positioning' => Positioning questions score
     *  'atomesCrochus' => AtomesCrochus questions score
     * ]
     */
    private function calculateProPersonalAffinityValue(array $mainAllAnswers, array $withAllAnswers): array
    {
        $mainQuestionsAnswers = $this->transformAnswerIntoQuestionsAnswers($mainAllAnswers['form_response']['answers']);
        $withQuestionsAnswers = $this->transformAnswerIntoQuestionsAnswers($withAllAnswers['form_response']['answers']);

        $scores = [];

        //---------------//
        //--- Profile ---//
        //---------------//
        // Title (homme/femme)
        $profileAffinityScore = $this->calculateTitleScore($mainQuestionsAnswers[self::PRO_TITLE_ID] ?? null, $withQuestionsAnswers[self::PERSONAL_SEARCHED_TITLE_ID] ?? null);
        dump('title score = ' . $profileAffinityScore);
        // Experience
        $experienceScore = $this->calculateExperienceScore($mainQuestionsAnswers[self::PRO_EXPERIENCE_ID] ?? null, $withQuestionsAnswers[self::PERSONAL_SEARCHED_EXPERIENCE_ID] ?? null);
        dump('experience score = ' . $experienceScore);
        $profileAffinityScore += $experienceScore;
        // Total profile score
        dump('Profile score = ' . $profileAffinityScore);
        $scores['profile'] = $profileAffinityScore * 100 / 25.0;
        dump('$scores["profile"]= ' . $scores['profile']);

        //---------------//
        //--- Passion ---//
        //---------------//
        // Hobby
        $passionAffinityScore = $this->calculateHobbyScore($mainQuestionsAnswers[self::PRO_HOBBY_ID] ?? null, $mainQuestionsAnswers[self::PRO_HOBBY_LEVEL_ID] ?? 0, $withQuestionsAnswers[self::PERSONAL_SEARCHED_HOBBIES_ID] ?? []);
        dump('hobby score = ' . $passionAffinityScore);
        // Pro passion website
        if (isset($mainQuestionsAnswers['DbdnZwCAWaOR'])) {
            $passionAffinityScore += 10;
            dump('pro passion website = 10');
        }
        // Advise domain
        $adviceScore = $this->calculateAdvicesScore($mainQuestionsAnswers[self::PRO_ADVICES_ID] ?? null, $withQuestionsAnswers[self::PERSONAL_SEARCHED_ADVICES_ID] ?? null);
        dump('advice score = ' . $adviceScore);
        $passionAffinityScore += $adviceScore;
        // Total passion score
        dump('Passion score = ' . $passionAffinityScore);
        $scores['passion'] = $passionAffinityScore * 100 / 45.0;
        dump('$scores["passion"]= ' . $scores['passion']);

        //-------------------//
        //--- Positioning ---//
        //-------------------//
        // Price : TODO supprimé pour le particulier => toujours égal à 10
        $positioningScore = $this->calculatePriceScore($mainQuestionsAnswers['KwwCbCkzNoro'] ?? [], $withQuestionsAnswers[self::PERSONAL_BUDGET_ID] ?? null);
        dump('price score = ' . $positioningScore);
        // Brand : no question in personal form about brands TODO
        // Vehicle type : TODO supprimé pour le particulier => toujours égal à 10
        $vehicleTypeScore = $this->calculateVehicleTypeScore($mainQuestionsAnswers[self::PRO_VEHICLE_BODY_ID] ?? [], $withQuestionsAnswers['TgCx9GnZokcZ'] ?? []);
        dump('vehicleType score = ' . $vehicleTypeScore);
        $positioningScore += $vehicleTypeScore;

        $scores['positioning'] = $positioningScore * 100 / 20.0;
        dump('$scores["positioning"]= ' . $scores['positioning']);

        //----------------------//
        //--- Atomes Crochus ---//
        //----------------------//
        // Hobbies
        $atomesCrochusScore = $this->calculateHobbiesScore($mainQuestionsAnswers['m57Ls95xJ5Ca'] ?? [], $withQuestionsAnswers['VxsHHmccvxvy'] ?? []);
        dump('hobbies score = ' . $atomesCrochusScore);
        // Road
        $roadScore = $this->calculateRoadScore($mainQuestionsAnswers['Q7QBuiJTCKDB'] ?? null, $withQuestionsAnswers['smLmytwZXVX0'] ?? null);
        dump('road score = ' . $roadScore);
        $atomesCrochusScore += $roadScore;
        // Total atomesCrochus score
        $scores['atomesCrochus'] = $atomesCrochusScore * 100 / 40.0;
        dump('$scores["atomesCrochus"]= ' . $scores['atomesCrochus']);

        // Total affinity score
        $scores['affinity'] = $scores['profile'] * self::POURCENTAGE_SCORE_PROFILE +
            $scores['passion'] * self::POURCENTAGE_SCORE_PASSION +
            $scores['positioning'] * self::POURCENTAGE_SCORE_POSITIONING +
            $scores['atomesCrochus'] * self::POURCENTAGE_SCORE_ATOMES;
        dump('$scores["affinity"]= ' . $scores['affinity']);
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

            // Title (genere)
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
                        ['%advices%' => join(', ', array_map("strtolower", $userQuestionsAnswers[self::PRO_ADVICES_ID]))]);
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
                } else {
                    $profilDescription .= PHP_EOL . $this->translator->trans('user.project.prefill.profesional.description.first_contact_preference.acting');
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

            // Disponibilités (cbz7epXYGnal)
            $disponibiliteId = 'cbz7epXYGnal';
            if (!empty($userQuestionsAnswers[$disponibiliteId])) {
                if (is_array($userQuestionsAnswers[$disponibiliteId])) {
                    $personalUser->setContactAvailabilities(json_encode($userQuestionsAnswers[$disponibiliteId]));
                }
            }

            // Préférence de 1er contact (EMQUuMj7kjeG)
            if (!empty($userQuestionsAnswers['EMQUuMj7kjeG'])) {
                if ($userQuestionsAnswers['EMQUuMj7kjeG'] == "que l'on vienne à vous") {
                    $personalUser->setFirstContactPreference(FirstContactPreference::I_M_WAITING());
                } elseif ($userQuestionsAnswers['EMQUuMj7kjeG'] == "faire le premier pas") {
                    $personalUser->setFirstContactPreference(FirstContactPreference::I_WILL_BEGIN());
                }
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
            if (!empty($userQuestionsAnswers[self::PERSONAL_USAGE_ID])) {
                if (empty($userQuestionsAnswers[self::PERSONAL_NEW_USED_ID])) {
                    $projectDescription .= $this->translator->trans('user.project.prefill.personal.description.search');
                } else {
                    $projectDescription .= ' ';
                }
                if ($userQuestionsAnswers[self::PERSONAL_USAGE_ID] == "Pour votre société") {
                    $projectDescription .= $this->translator->trans('user.project.prefill.personal.description.usage.company');
                } else {
                    $projectDescription .= $this->translator->trans('user.project.prefill.personal.description.usage.personal');
                }

                if ($userQuestionsAnswers[self::PERSONAL_USAGE_ID] == "Pour votre société") {
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
                $projectDescription .= strtolower($userQuestionsAnswers[self::PERSONAL_ENERGY_ID]);
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
                    ['%generation%' => strtolower($userQuestionsAnswers[self::PERSONAL_GENERATION_ID])]);
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
            $personalUser->getProject()->setIsFleet(!empty($userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID]) &&
                ($userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID] > 1 || $userQuestionsAnswers[self::PERSONAL_NB_VEHICLE_ID] == "Plus de 10"));
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
     * Calculate the score about Passion
     * @param string $proAnswer
     * @param int $levelProAnswer
     * @param array $personalAnswer
     * @return float
     */
    private function calculateHobbyScore(?string $proAnswer, int $levelProAnswer = 0, array $personalAnswer): float
    {
        if (count($personalAnswer) === 0 || in_array("Non, pas vraiment", $personalAnswer)) {
            return 25;
        } elseif ($proAnswer != null && in_array($proAnswer, $personalAnswer)) {
            return 15 + $levelProAnswer * 2;
        }
        return 0;
    }

    /**
     * Calculate the score about Advices
     * @param null|string $proAnswer
     * @param null|string $personalAnswer
     * @return float
     */
    private function calculateAdvicesScore(?string $proAnswer, ?string $personalAnswer): float
    {
        if ($personalAnswer === null || strtolower($proAnswer) === strtolower($personalAnswer)) {
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
        foreach ($proAnswer as $priceRance) {
            switch ($priceRance) {
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
     * Calculate the score about the vehicle types
     * @param array $proAnswer
     * @param array $personalAnswer
     * @return float
     */
    private function calculateVehicleTypeScore(array $proAnswer, array $personalAnswer): float
    {
        return $this->formule1($proAnswer, $personalAnswer, 10);
    }

    /**
     * Calculate the score about the hobbies
     * @param array $proAnswer
     * @param array $personalAnswer
     * @return float
     */
    private function calculateHobbiesScore(array $proAnswer, array $personalAnswer): float
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
        if (count($personalAnswers) === 0) {
            return $maxPoint;
        }
        $intersection = array_intersect($proAnswers, $personalAnswers);
        return $maxPoint * count($intersection) / count($proAnswers);
    }
}