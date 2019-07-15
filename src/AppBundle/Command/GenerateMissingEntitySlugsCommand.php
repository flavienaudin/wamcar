<?php

namespace AppBundle\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wamcar\Garage\Garage;
use Wamcar\User\BaseUser;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\ProVehicle;

class GenerateMissingEntitySlugsCommand extends BaseCommand
{
    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:generate:missingEntitySlugs')
            ->setDescription('Generate missing "slug" of sluggable entities')
            ->addOption('only-empty-slug', null, InputOption::VALUE_NONE,
                'Update only the empty slug. Otherwise all slugs are regenerated.')
            ->addOption('entity', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Array of entities which slug will be (re)generated. ALl entities are concerned if not provided.
                --entity=personal_user --entity=pro_user --entity=personal_vehicle --entity=pro_vehicle --entity=garage',
                ["personal_user", "pro_user", "personal_vehicle", "pro_vehicle", "garage"]);
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $entities = ["personal_user" => false, "pro_user" => false, "personal_vehicle" => false, "pro_vehicle" => false, "garage" => false];
        foreach ($input->getOption("entity") as $entity) {
            $entities[$entity] = true;
        }

        // Pro Users
        if ($entities['pro_user']) {
            $proUserRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrineProUserRepository');
            $proUsers = $proUserRepository->findForSlugGeneration($input->getOption('only-empty-slug'), true);

            $io->text("Pro users");
            $io->progressStart(count($proUsers));
            /** @var BaseUser $proUser */
            foreach ($proUsers as $proUser) {
                $io->progressAdvance();
                if ($proUser->getSlug() == null) {
                    // Pour obliger la mise à jour (si pas de modification de l'entité, pas d'update)
                    $proUser->setSlug('');
                    $proUserRepository->update($proUser);
                    $proUser->setSlug(null);
                    $proUserRepository->update($proUser);
                } else {
                    $proUser->setSlug(null);
                    $proUserRepository->update($proUser);
                }
            }
            $io->progressFinish();
        }

        // Personal Users
        if ($entities['personal_user']) {
            $personalUserRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository');
            $personalUsers = $personalUserRepository->findForSlugGeneration($input->getOption('only-empty-slug'), true);

            $io->text("Personal users");
            $io->progressStart(count($personalUsers));
            /** @var BaseUser $personalUser */
            foreach ($personalUsers as $personalUser) {
                $io->progressAdvance();
                if ($personalUser->getSlug() == null) {
                    // Pour obliger la mise à jour (si pas de modification de l'entité, pas d'update)
                    $personalUser->setSlug('');
                    $proUserRepository->update($personalUser);
                    $personalUser->setSlug(null);
                    $proUserRepository->update($personalUser);
                } else {
                    $personalUser->setSlug(null);
                    $proUserRepository->update($personalUser);
                }
            }
            $io->progressFinish();
        }

        // Pro Vehicles
        if ($entities['pro_vehicle']) {
            $io->text("Pro Vehicles");
            $proVehicleRepository = $this->getContainer()->get('Wamcar\Vehicle\ProVehicleRepository');

            $proVehicles = $proVehicleRepository->findForSlugGeneration($input->getOption('only-empty-slug'), true);
            $io->progressStart(count($proVehicles));
            /** @var ProVehicle $proVehicle */
            foreach ($proVehicles as $proVehicle) {
                $io->progressAdvance();

                if (empty($proVehicle->getIsUsedSlugValue())) {
                    $proVehicle->setIsUsed($proVehicle->isUsed());
                }

                if ($proVehicle->getSlug() == null) {
                    // Pour obliger la mise à jour (si pas de modification de l'entité, pas d'update)
                    $proVehicle->setSlug('');
                    $proVehicleRepository->update($proVehicle);
                    $proVehicle->setSlug(null);
                    $proVehicleRepository->update($proVehicle);
                } else {
                    // Auto slug (re)generation
                    $proVehicle->setSlug(null);
                    $proVehicleRepository->update($proVehicle);
                }
            }
            $io->progressFinish();
        }

        // Personal Vehicles
        if ($entities['personal_vehicle']) {
            $io->text("Personal Vehicles");
            $personalVehicleRepository = $this->getContainer()->get('Wamcar\Vehicle\PersonalVehicleRepository');

            $personalVehicles = $personalVehicleRepository->findForSlugGeneration($input->getOption('only-empty-slug'), true);
            $io->progressStart(count($personalVehicles));
            /** @var PersonalVehicle $personalVehicle */
            foreach ($personalVehicles as $personalVehicle) {
                $io->progressAdvance();

                if (empty($personalVehicle->getIsUsedSlugValue())) {
                    $personalVehicle->setIsUsed($personalVehicle->isUsed());
                }

                if ($personalVehicle->getSlug() == null) {
                    // Pour obliger la mise à jour (si pas de modification de l'entité, pas d'update)
                    $personalVehicle->setSlug('');
                    $personalVehicleRepository->update($personalVehicle);
                    $personalVehicle->setSlug(null);
                    $personalVehicleRepository->update($personalVehicle);
                } else {
                    // Auto slug (re)generation
                    $personalVehicle->setSlug(null);
                    $personalVehicleRepository->update($personalVehicle);
                }
            }
            $io->progressFinish();
        }

        // Garage
        if ($entities['garage']) {
            $io->text("Garage");
            $garageRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrineGarageRepository');

            $garages = $garageRepository->findForSlugGeneration($input->getOption('only-empty-slug'), true);
            $io->progressStart(count($garages));
            /** @var Garage $garage */
            foreach ($garages as $garage) {
                $io->progressAdvance();

                if ($garage->getSlug() == null) {
                    // Pour obliger la mise à jour (si pas de modification de l'entité, pas d'update)
                    $garage->setSlug('');
                    $garageRepository->update($garage);
                    $garage->setSlug(null);
                    $garageRepository->update($garage);
                } else {
                    // Auto slug (re)generation
                    $garage->setSlug(null);
                    $garageRepository->update($garage);
                }
            }
            $io->progressFinish();
        }
        $io->success("Done at " . date(self::DATE_FORMAT));
    }
}