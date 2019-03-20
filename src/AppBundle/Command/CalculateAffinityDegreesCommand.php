<?php

namespace AppBundle\Command;


use AppBundle\Services\Affinity\AffinityAnswerCalculationService;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TypeForm\Doctrine\Entity\AffinityAnswer;
use TypeForm\Doctrine\Repository\DoctrineAffinityAnswerRepository;
use Wamcar\User\Event\AffinityDegreeCalculatedEvent;

class CalculateAffinityDegreesCommand extends BaseCommand
{

    /** @var DoctrineAffinityAnswerRepository $affinityAnswerRepository */
    private $affinityAnswerRepository;
    /** @var AffinityAnswerCalculationService $affinityAnswerCalculationService */
    private $affinityAnswerCalculationService;
    /** @var MessageBus $eventBus */
    private $eventBus;

    /**
     * CalculateAffinityDegreesCommand constructor.
     *
     * @param DoctrineAffinityAnswerRepository $affinityAnswerRepository
     * @param AffinityAnswerCalculationService $affinityAnswerCalculationService
     * @param MessageBus $eventBus
     */
    public function __construct(DoctrineAffinityAnswerRepository $affinityAnswerRepository, AffinityAnswerCalculationService $affinityAnswerCalculationService, MessageBus $eventBus)
    {
        parent::__construct();
        $this->affinityAnswerRepository = $affinityAnswerRepository;
        $this->affinityAnswerCalculationService = $affinityAnswerCalculationService;
        $this->eventBus = $eventBus;
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
        $io = new SymfonyStyle($input, $output);

        $untreatedPersonalAnswers = $this->affinityAnswerRepository->retrieveUntreatedPersonalAnswer();
        $treatedPersonalAnswers = $this->affinityAnswerRepository->retrieveTreatedPersonalAnswer();
        $untreatedProAnswers = $this->affinityAnswerRepository->retrieveUntreatedProAnswer();
        $treatedProAnswers = $this->affinityAnswerRepository->retrieveTreatedProAnswer();

        $newPersonalAffinityDegreeCalculation = count($untreatedPersonalAnswers) * (count($treatedProAnswers) + count($untreatedProAnswers));
        $io->text(sprintf('Treat %d untreated personal form answers for %d calculations', count($untreatedPersonalAnswers), $newPersonalAffinityDegreeCalculation));
        $io->progressStart($newPersonalAffinityDegreeCalculation);

        $outputText = [];
        /** @var AffinityAnswer $untreatedPersonalAnswer */
        foreach ($untreatedPersonalAnswers as $untreatedPersonalAnswer) {
            /** @var AffinityAnswer $treatedProAnswer */
            foreach ($treatedProAnswers as $treatedProAnswer) {
                $io->progressAdvance();
                $this->affinityAnswerCalculationService->calculateAffinityValue($untreatedPersonalAnswer, $treatedProAnswer);
                // symetric score
                //$this->affinityAnswerCalculationService->calculateAffinityValue($treatedProAnswer, $untreatedPersonalAnswer);

                $outputText[] = sprintf('%d %s %s %s -> (treated) %d %s %s %s',
                    $untreatedPersonalAnswer->getUser()->getId(),
                    $untreatedPersonalAnswer->getUser()->getFullName(),
                    $untreatedPersonalAnswer->getUser()->getType(),
                    $untreatedPersonalAnswer->getFormId(),
                    $treatedProAnswer->getUser()->getId(),
                    $treatedProAnswer->getUser()->getFullName(),
                    $treatedProAnswer->getUser()->getType(),
                    $treatedProAnswer->getFormId()
                );
            }

            /** @var AffinityAnswer $untreatedProAnswer */
            foreach ($untreatedProAnswers as $untreatedProAnswer) {
                $io->progressAdvance();
                $this->affinityAnswerCalculationService->calculateAffinityValue($untreatedPersonalAnswer, $untreatedProAnswer);
                // symetric score
                //$this->affinityAnswerCalculationService->calculateAffinityValue($untreatedProAnswer, $untreatedPersonalAnswer);

                $outputText[] = sprintf('%d %s %s %s -> (untreated) %d %s %s %s',
                    $untreatedPersonalAnswer->getUser()->getId(),
                    $untreatedPersonalAnswer->getUser()->getFullName(),
                    $untreatedPersonalAnswer->getUser()->getType(),
                    $untreatedPersonalAnswer->getFormId(),
                    $untreatedProAnswer->getUser()->getId(),
                    $untreatedProAnswer->getUser()->getFullName(),
                    $untreatedProAnswer->getUser()->getType(),
                    $untreatedProAnswer->getFormId()
                );
            }

            $untreatedPersonalAnswer->setTreatedAt(new \DateTime('now'));
            $this->affinityAnswerRepository->update($untreatedPersonalAnswer);
            $this->eventBus->handle(new AffinityDegreeCalculatedEvent($untreatedPersonalAnswer->getUser()));
        }
        $io->progressFinish();
        $io->listing($outputText);

        $newProAffinityDegreeCalculation = count($untreatedProAnswers) * (count($treatedPersonalAnswers));
        $io->text(sprintf('Treat %d untreated pro form answers  for %d calculations', count($untreatedProAnswers), $newProAffinityDegreeCalculation));

        $outputText = [];
        $io->progressStart($newProAffinityDegreeCalculation);
        /** @var AffinityAnswer $untreatedProAnswer */
        foreach ($untreatedProAnswers as $untreatedProAnswer) {
            /** @var AffinityAnswer $treatedPersonalAnswer */
            foreach ($treatedPersonalAnswers as $treatedPersonalAnswer) {
                $io->progressAdvance();
                $this->affinityAnswerCalculationService->calculateAffinityValue($untreatedProAnswer, $treatedPersonalAnswer);
                // symetric score
                //$this->affinityAnswerCalculationService->calculateAffinityValue($treatedPersonalAnswer, $untreatedProAnswer);

                $outputText[] = sprintf('%d %s %s %s -> (treated) %d %s %s %s',
                    $untreatedProAnswer->getUser()->getId(),
                    $untreatedProAnswer->getUser()->getFullName(),
                    $untreatedProAnswer->getUser()->getType(),
                    $untreatedProAnswer->getFormId(),
                    $treatedPersonalAnswer->getUser()->getId(),
                    $treatedPersonalAnswer->getUser()->getFullName(),
                    $treatedPersonalAnswer->getUser()->getType(),
                    $treatedPersonalAnswer->getFormId()
                );
            }

            $untreatedProAnswer->setTreatedAt(new \DateTime('now'));
            $this->affinityAnswerRepository->update($untreatedProAnswer);
            $this->eventBus->handle(new AffinityDegreeCalculatedEvent($untreatedProAnswer->getUser()));
        }
        $io->progressFinish();
        $io->listing($outputText);
        $io->success('Done !');
    }
}