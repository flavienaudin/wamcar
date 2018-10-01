<?php

namespace AppBundle\Services\Affinity;


use AppBundle\Doctrine\Repository\DoctrineAffinityDegreeRepository;
use TypeForm\Doctrine\Entity\AffinityAnswer;
use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;

class AffinityDegreeCalculationService
{

    const POURCENTAGE_SCORE_PROFILE = 0.25;
    const POURCENTAGE_SCORE_PASSION = 0.25;
    const POURCENTAGE_SCORE_POSITIONING = 0.25;
    const POURCENTAGE_SCORE_ATOMES = 0.25;

    /** @var DoctrineAffinityDegreeRepository $affinityDegreeRepository */
    private $affinityDegreeRepository;

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
            $cores =  $this->calculateProPersonalAffinityValue($mainAffinityAnswer->getContentAsArray(), $withAffinityAnswer->getContentAsArray());
        } elseif ($mainAffinityAnswer->getUser() instanceof PersonalUser && $withAffinityAnswer->getUser() instanceof ProUser) {
            // Score Pro = score Personal. If assymetric calculation, implement a different function.
            $cores =   $this->calculateProPersonalAffinityValue($withAffinityAnswer->getContentAsArray(), $mainAffinityAnswer->getContentAsArray());
        }
        if(!is_array($scores)){
            return null;
        }

        if($mainAffinityAnswer->getUser()){
            $this->affinityDegreeRepository->update();
        }

        return $cores;
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
        dump($mainQuestionsAnswers);
        dump($withQuestionsAnswers);

        //---------------//
        //--- Profile ---//
        //---------------//
        // Title
        $profileAffinityScore = $this->calculateTitleScore($mainQuestionsAnswers['N20Fa9XvCxe3'] ?? null, $withQuestionsAnswers['rKajnOTWhQ11'] ?? null);
        dump('title score = ' . $profileAffinityScore);
        // Experience
        $experienceScore = $this->calculateExperienceScore($mainQuestionsAnswers['pXwMCaGPIkYn'] ?? null, $withQuestionsAnswers['HulaGSXnxphU'] ?? null);
        dump('experience score = ' . $experienceScore);
        $profileAffinityScore += $experienceScore;
        // Total profile score
        dump('Profile score = ' . $profileAffinityScore);
        $scores['profile'] = $profileAffinityScore * self::POURCENTAGE_SCORE_PROFILE / 25.0;

        //---------------//
        //--- Passion ---//
        //---------------//
        // Passions
        $passionAffinityScore = $this->calculatePassionsScore($mainQuestionsAnswers['QrrfsAj21VTe'] ?? null, $mainQuestionsAnswers['Ik34QcWAoM7O'] ?? null, $withQuestionsAnswers['WZnVUWj4HHsW'] ?? []);
        dump('passions score = ' . $passionAffinityScore);
        // Pro passion website
        if (isset($mainQuestionsAnswers['DbdnZwCAWaOR'])) {
            $passionAffinityScore += 10;
        }
        dump('pro passion website = 10');
        // Advise domain
        $adviceScore = $this->calculateAdvicesScore($mainQuestionsAnswers['VNAGSbICaJmB'] ?? null, $withQuestionsAnswers['a1jPPIiYk1pu'] ?? null);
        dump('advice score = ' . $adviceScore);
        $passionAffinityScore += $adviceScore;
        // Total passion score
        dump('Passion score = ' . $passionAffinityScore);
        $scores['passion'] = $passionAffinityScore * self::POURCENTAGE_SCORE_PASSION / 45.0;

        //-------------------//
        //--- Positioning ---//
        //-------------------//
        // Price
        $positioningScore = $this->calculatePriceScore($mainQuestionsAnswers['KwwCbCkzNoro'] ?? [], $withQuestionsAnswers['OXDcMxNY7jXK'] ?? null);
        dump('price score = ' . $positioningScore);
        // Brand : no question in personal form about brands TODO
        // Vehicle type
        $vehicleTypeScore = $this->calculateVehicleTypeScore($mainQuestionsAnswers['Sn72hV3LGlkh'] ?? [], $withQuestionsAnswers['TgCx9GnZokcZ'] ?? []);
        dump('vehicleType score = ' . $vehicleTypeScore);
        $positioningScore += $vehicleTypeScore;

        $scores['positioning'] = $positioningScore * self::POURCENTAGE_SCORE_POSITIONING / 20.0;

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
        $scores['atomesCrochus'] = $atomesCrochusScore * self::POURCENTAGE_SCORE_ATOMES / 40.0;

        // Total affinity score
        $scores['affinity'] = $scores['profile'] + $scores['passion'] + $scores['positioning'] + $scores['atomesCrochus'];
        dump('$scores["affinity"]= ' . $scores['affinity']);
        return $scores;
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
        switch ($personalAnswer) {
            case "au moins 20 ans":
                if ($proAnswer === "Plus de 20 ans") {
                    return 15;
                }
                break;
            case "au moins 10 ans":
                if ($proAnswer === "10 à 20 ans" || $proAnswer === "Plus de 20 ans") {
                    return 15;
                }
                break;
            case "au moins 5 ans":
                if ($proAnswer === "5 à 10 ans" || $proAnswer === "10 à 20 ans" || $proAnswer === "Plus de 20 ans") {
                    return 15;
                }
                break;
            case "peu importe":
            case null:
                return 15;
                break;
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
    private function calculatePassionsScore(?string $proAnswer, ?int $levelProAnswer, array $personalAnswer): float
    {
        if (count($personalAnswer) === 0 || in_array("Non, pas vraiment", $personalAnswer)) {
            return 10;
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
     * Calculate the score about advices domain
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