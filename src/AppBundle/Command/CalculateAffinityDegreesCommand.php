<?php

namespace AppBundle\Command;


use AppBundle\Services\Affinity\AffinityDegreeCalculationService;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TypeForm\Doctrine\Entity\AffinityAnswer;
use TypeForm\Doctrine\Repository\DoctrineAffinityAnswerRepository;

class CalculateAffinityDegreesCommand extends BaseCommand
{

    /** @var DoctrineAffinityAnswerRepository $affinityAnswerRepository */
    private $affinityAnswerRepository;
    /** @var AffinityDegreeCalculationService $affinityDegreeCalculationService */
    private $affinityDegreeCalculationService;

    /**
     * CalculateAffinityDegreesCommand constructor.
     *
     * @param DoctrineAffinityAnswerRepository $affinityAnswerRepository
     * @param AffinityDegreeCalculationService $affinityDegreeCalculationService
     */
    public function __construct(DoctrineAffinityAnswerRepository $affinityAnswerRepository, AffinityDegreeCalculationService $affinityDegreeCalculationService)
    {
        parent::__construct();
        $this->affinityAnswerRepository = $affinityAnswerRepository;
        $this->affinityDegreeCalculationService = $affinityDegreeCalculationService;
    }


    /**
     * @inheritDoc
     */
    protected function configure()
    {

        $this
            ->setName('wamcar:calculate:affinity_degrees')
            ->setDescription('Calculate missing affinity degrees');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $untreatedProAnswers = $this->affinityAnswerRepository->retrieveUntreatedProAnswer();
        $treatedProAnswers = $this->affinityAnswerRepository->retrieveTreatedProAnswer();
        $untreatedPersonalAnswers = $this->affinityAnswerRepository->retrieveUntreatedPersonalAnswer();
        $treatedPersonalAnswers = $this->affinityAnswerRepository->retrieveTreatedPersonalAnswer();

        $newPersonalAffinityDegreeCalculation = count($untreatedPersonalAnswers) * (count($treatedProAnswers) + count($untreatedProAnswers));
        $this->log("info", sprintf('Treat %d untreated personal form answers for %d calculations', count($untreatedPersonalAnswers), $newPersonalAffinityDegreeCalculation));
        $progress = new ProgressBar($this->output, $newPersonalAffinityDegreeCalculation);
        /** @var AffinityAnswer $untreatedPersonalAnswer */
        foreach ($untreatedPersonalAnswers as $untreatedPersonalAnswer) {
            $this->log('info', 'with treated pros');
            /** @var AffinityAnswer $treatedProAnswer */
            foreach ($treatedProAnswers as $treatedProAnswer) {
                $progress->advance();

                $this->affinityDegreeCalculationService->calculateAffinityValue($untreatedPersonalAnswer, $treatedProAnswer);
                $this->affinityDegreeCalculationService->calculateAffinityValue($treatedProAnswer, $untreatedPersonalAnswer);

                $this->logCRLF();
                $this->log("info", sprintf('%d %s %s %s -> %d %s %s %s',
                    $untreatedPersonalAnswer->getId(),
                    $untreatedPersonalAnswer->getUser()->getFullName(),
                    $untreatedPersonalAnswer->getUser()->getType(),
                    $untreatedPersonalAnswer->getFormId(),
                    $treatedProAnswer->getId(),
                    $treatedProAnswer->getUser()->getFullName(),
                    $treatedProAnswer->getUser()->getType(),
                    $treatedProAnswer->getFormId()
                ));
            }

            $this->log('info', 'with untreated pros');
            /** @var AffinityAnswer $untreatedProAnswer */
            foreach ($untreatedProAnswers as $untreatedProAnswer) {
                $progress->advance();
                $this->affinityDegreeCalculationService->calculateAffinityValue($untreatedPersonalAnswer, $untreatedProAnswer);
                $this->affinityDegreeCalculationService->calculateAffinityValue($untreatedProAnswer, $untreatedPersonalAnswer);

                $this->logCRLF();
                $this->log("info", sprintf('%d %s %s %s -> %d %s %s %s',
                    $untreatedPersonalAnswer->getId(),
                    $untreatedPersonalAnswer->getUser()->getFullName(),
                    $untreatedPersonalAnswer->getUser()->getType(),
                    $untreatedPersonalAnswer->getFormId(),
                    $untreatedProAnswer->getId(),
                    $untreatedProAnswer->getUser()->getFullName(),
                    $untreatedProAnswer->getUser()->getType(),
                    $untreatedProAnswer->getFormId()
                ));
            }


        }
        $progress->finish();
        $this->logCRLF();

        $newProAffinityDegreeCalculation = count($untreatedProAnswers) * (count($treatedPersonalAnswers));
        $this->log("info", sprintf('Treat %d untreated pro form answers  for %d calculations', count($untreatedProAnswers), $newProAffinityDegreeCalculation));
        $progressBis = new ProgressBar($this->output, $newProAffinityDegreeCalculation);
        /** @var AffinityAnswer $untreatedProAnswer */
        foreach ($untreatedProAnswers as $untreatedProAnswer) {
            $this->log('info', 'with treated personals');
            /** @var AffinityAnswer $treatedPersonalAnswer */
            foreach ($treatedPersonalAnswers as $treatedPersonalAnswer) {
                $progressBis->advance();
                $this->affinityDegreeCalculationService->calculateAffinityValue($untreatedProAnswer, $treatedPersonalAnswer);
                $this->affinityDegreeCalculationService->calculateAffinityValue($treatedPersonalAnswer, $untreatedProAnswer);

                $this->logCRLF();
                $this->log("info", sprintf('%d %s %s %s -> %d %s %s %s',
                    $untreatedProAnswer->getId(),
                    $untreatedProAnswer->getUser()->getFullName(),
                    $untreatedProAnswer->getUser()->getType(),
                    $untreatedProAnswer->getFormId(),
                    $treatedPersonalAnswer->getId(),
                    $treatedPersonalAnswer->getUser()->getFullName(),
                    $treatedPersonalAnswer->getUser()->getType(),
                    $treatedPersonalAnswer->getFormId()
                ));
            }
        }
        $progressBis->finish();
        $this->logCRLF();
        $this->log('success', 'Done !');
    }
}