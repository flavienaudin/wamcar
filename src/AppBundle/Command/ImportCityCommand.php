<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportCityCommand extends BaseCommand
{
    /** @var EntityManager */
    private $em;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:city')
            ->setDescription('Populate the city info table with data from an CSV file')
            ->addArgument(
                'file',
                InputArgument::OPTIONAL,
                'The path of the CSV file to load (default: fixture data)',
                __DIR__ . '/../../../database/fixtures/cities.csv'
            );
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
        $io->text("Start at " . date(self::DATE_FORMAT));
        $filename = $input->getArgument('file');
        // Importing CSV on DB via Doctrine ORM
        $this->import($io, $filename);
        $io->success("Done at " . date(self::DATE_FORMAT));
    }

    private function import(SymfonyStyle $io, $filename)
    {
        // Turning off doctrine default logs queries for saving memory
        $connection = $this->em->getConnection();
        $connection->getConfiguration()->setSQLLogger(null);

        $data = $this->convertCsvToArray($filename);

        $size = count($data);
        $batchSize = 500;
        $i = 1;

        // Starting progress
        $io->progressStart($size);
        $stmt = $connection->prepare("TRUNCATE TABLE city");
        $stmt->execute();
        foreach ($data as $row) {
            list($lat, $lon) = explode(',', $row[5]);
            $insertValues[] = "(\"$row[0]\", \"$row[1]\", \"$row[2]\", \"$lat\", \"$lon\", \"$row[6]\", \"$row[7]\", \"$row[3]\", \"$row[4]\")";

            // Each 500 cities persisted we flush everything
            if (($i % $batchSize) === 0) {

                $sql = 'REPLACE INTO city (insee, city_postal_code, city_name, city_latitude, city_longitude, code_departement, code_region, departement, region) VALUES ';
                $sql .= implode(',', $insertValues);
                $stmt = $connection->prepare($sql);
                $stmt->execute();
                // Advancing for progress bar
                $io->progressAdvance($batchSize);
                $insertValues = [];
            }

            $i++;
        }

        $sql = 'REPLACE INTO city (insee, city_postal_code, city_name, city_latitude, city_longitude, code_departement, code_region, departement, region) VALUES ';
        $sql .= implode(',', $insertValues);
        $stmt = $connection->prepare($sql);
        $stmt->execute();

        $io->progressFinish();
    }

    /**
     * @param        $filename
     * @param array $header
     * @param string $delimiter
     * @return array|bool
     */
    private function convertCsvToArray($filename, $delimiter = ';', $skipFirstLine = true)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 10000, $delimiter)) !== false) {
                if ($skipFirstLine) {
                    $skipFirstLine = !$skipFirstLine;
                    continue;
                }
                $data[] = $row;
            }
            fclose($handle);
        }
        return $data;
    }

}
