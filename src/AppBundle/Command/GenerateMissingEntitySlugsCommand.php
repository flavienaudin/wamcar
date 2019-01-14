<?php

namespace AppBundle\Command;


use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
        $this->output = $output;
        $entities = ["personal_user" => false, "pro_user" => false, "personal_vehicle" => false, "pro_vehicle" => false, "garage" => false];
        foreach ($input->getOption("entity") as $entity) {
            $entities[$entity] = true;
        }

        // Pro Users
        if ($entities['pro_user']) {
            $proUserRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrineProUserRepository');
            $proUsers = $proUserRepository->findForSlugGeneration($input->getOption('only-empty-slug'), true);

            $this->log(self::INFO, "Pro users");
            $progressProUsers = new ProgressBar($this->output, count($proUsers));
            /** @var BaseUser $proUser */
            foreach ($proUsers as $proUser) {
                $progressProUsers->advance();
                if ($proUser->getSlug() == null) {
                    // Pour obliger la mise à jour (si pas de modification de l'entité, pas d'update)
                    $proUser->setSlug('');
                    $proUserRepository->update($proUser);
                    $proUser->setSlug(null);
                    $proUserRepository->update($proUser);
                }else {
                    $proUser->setSlug(null);
                    $proUserRepository->update($proUser);
                }
            }
            $progressProUsers->finish();
            $this->logCRLF();
        }

        // Personal Users
        if ($entities['personal_user']) {
            $personalUserRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrinePersonalUserRepository');
            $personalUsers = $personalUserRepository->findForSlugGeneration($input->getOption('only-empty-slug'), true);

            $this->log(self::INFO, "Personal users");
            $progressPersonalUsers = new ProgressBar($this->output, count($personalUsers));
            /** @var BaseUser $personalUser */
            foreach ($personalUsers as $personalUser) {
                $progressPersonalUsers->advance();
                if ($personalUser->getSlug() == null) {
                    // Pour obliger la mise à jour (si pas de modification de l'entité, pas d'update)
                    $personalUser->setSlug('');
                    $proUserRepository->update($personalUser);
                    $personalUser->setSlug(null);
                    $proUserRepository->update($personalUser);
                }else {
                    $personalUser->setSlug(null);
                    $proUserRepository->update($personalUser);
                }
            }
            $progressPersonalUsers->finish();
            $this->logCRLF();
        }

        // Pro Vehicles
        if ($entities['pro_vehicle']) {
            $this->log(self::INFO, "Pro Vehicles");
            $proVehicleRepository = $this->getContainer()->get('Wamcar\Vehicle\ProVehicleRepository');

            $proVehicles = $proVehicleRepository->findForSlugGeneration($input->getOption('only-empty-slug'), true);
            $progressProVehicles = new ProgressBar($this->output, count($proVehicles));
            /** @var ProVehicle $proVehicle */
            foreach ($proVehicles as $proVehicle) {
                $progressProVehicles->advance();

                if (empty($proVehicle->getIsUsedSlugValue())) {
                    $proVehicle->setIsUsed($proVehicle->isUsed());
                }

                if ($proVehicle->getSlug() == null) {
                    // Pour obliger la mise à jour (si pas de modification de l'entité, pas d'update)
                    $proVehicle->setSlug('');
                    $proVehicleRepository->update($proVehicle);
                    $proVehicle->setSlug(null);
                    $proVehicleRepository->update($proVehicle);
                }else{
                    // Auto slug (re)generation
                    $proVehicle->setSlug(null);
                    $proVehicleRepository->update($proVehicle);
                }
            }
            $progressProVehicles->finish();
            $this->logCRLF();
        }

        // Personal Vehicles
        if ($entities['personal_vehicle']) {
            $this->log(self::INFO, "Personal Vehicles");
            $personalVehicleRepository = $this->getContainer()->get('Wamcar\Vehicle\PersonalVehicleRepository');

            $personalVehicles = $personalVehicleRepository->findForSlugGeneration($input->getOption('only-empty-slug'), true);
            $progressPersonalVehicles = new ProgressBar($this->output, count($personalVehicles));
            /** @var PersonalVehicle $personalVehicle */
            foreach ($personalVehicles as $personalVehicle) {
                $progressPersonalVehicles->advance();

                if (empty($personalVehicle->getIsUsedSlugValue())) {
                    $personalVehicle->setIsUsed($personalVehicle->isUsed());
                }

                if ($personalVehicle->getSlug() == null) {
                    // Pour obliger la mise à jour (si pas de modification de l'entité, pas d'update)
                    $personalVehicle->setSlug('');
                    $personalVehicleRepository->update($personalVehicle);
                    $personalVehicle->setSlug(null);
                    $personalVehicleRepository->update($personalVehicle);
                }else{
                    // Auto slug (re)generation
                    $personalVehicle->setSlug(null);
                    $personalVehicleRepository->update($personalVehicle);
                }
            }
            $progressPersonalVehicles->finish();
            $this->logCRLF();
        }

        // Garage
        if ($entities['garage']) {
            $this->log(self::INFO, "Garage");
            $garageRepository = $this->getContainer()->get('AppBundle\Doctrine\Repository\DoctrineGarageRepository');

            $garages = $garageRepository->findForSlugGeneration($input->getOption('only-empty-slug'), true);
            $progressGarages= new ProgressBar($this->output, count($garages));
            /** @var Garage $garage */
            foreach ($garages as $garage) {
                $progressGarages->advance();

                if ($garage->getSlug() == null) {
                    // Pour obliger la mise à jour (si pas de modification de l'entité, pas d'update)
                    $garage->setSlug('');
                    $garageRepository->update($garage);
                    $garage->setSlug(null);
                    $garageRepository->update($garage);
                }else{
                    // Auto slug (re)generation
                    $garage->setSlug(null);
                    $garageRepository->update($garage);
                }
            }
            $progressGarages->finish();
            $this->logCRLF();
        }

        $this->log('success', 'Done !');
    }
}