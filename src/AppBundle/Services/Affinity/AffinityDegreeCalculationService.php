<?php

namespace AppBundle\Services\Affinity;


use AppBundle\Doctrine\Entity\AffinityDegree;
use AppBundle\Doctrine\Repository\DoctrineAffinityDegreeRepository;
use TypeForm\Doctrine\Entity\AffinityAnswer;
use Wamcar\User\ProUser;

class AffinityDegreeCalculationService
{

    /** @var DoctrineAffinityDegreeRepository $affinityDegreeRepository */
    private $affinityDegreeRepository;

    public function calculateAffinityValue(AffinityAnswer $mainAffinityAnswer, AffinityAnswer $withAffinityAnswer): float
    {
        if($mainAffinityAnswer->getUser() instanceof ProUser) {
            return $this->calculateProPersonalAffinityValue($mainAffinityAnswer->getContentAsArray(), $withAffinityAnswer->getContentAsArray());
        }else{
            return $this->calculatePersonalProAffinityValue($mainAffinityAnswer->getContentAsArray(), $withAffinityAnswer->getContentAsArray());
        }
    }

    private function calculateProPersonalAffinityValue(array $mainFullAnswer, array $withFullAnswer): float
    {
        $affinityValue = 0.0;

        $mainQuestionsAnswers = $this->transformAnswerIntoQuestionsAnswers($mainFullAnswer['form_response']['answers']);
        $withQuestionsAnswers = $this->transformAnswerIntoQuestionsAnswers($withFullAnswer['form_response']['answers']);

        dump($mainQuestionsAnswers);
        dump($withQuestionsAnswers);

        return $affinityValue;
    }

    private function calculatePersonalProAffinityValue(array $mainAnswer, array $withAnswer): float
    {
        $affinityValue = 0.0;

        // TODO implement calculation

        return $affinityValue;
    }

    /**
     * @param array $answers
     * @return array
     */
    private function transformAnswerIntoQuestionsAnswers(array $answers):array
    {
        $questionsAnswers = [];
        foreach ($answers as $answer){
            if(in_array($answer['type'], ['text', 'number','url'])) {
                $questionsAnswers[$answer['field']['id']] = $answer[$answer['type']];
            }elseif($answer['type'] == 'choice'){
                if(isset($answer[$answer['type']]['label'])) {
                    $questionsAnswers[$answer['field']['id']] = $answer[$answer['type']]['label'];
                }elseif($answer[$answer['type']]['other']){
                    $questionsAnswers[$answer['field']['id']] = $answer[$answer['type']]['other'];
                }
            }elseif($answer['type'] == 'choices'){
                $labelAnswers = [];
                if(isset($answer[$answer['type']]['labels'])) {
                    $labelAnswers = $answer[$answer['type']]['labels'];
                }
                if(isset($answer[$answer['type']]['other'])){
                    $labelAnswers[] = $answer[$answer['type']]['other'];
                }
                $questionsAnswers[$answer['field']['id']] = $labelAnswers;
            }
        }
        return $questionsAnswers;
    }

}